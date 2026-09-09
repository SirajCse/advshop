<template>
    <div class="content">
        <slot v-bind="{ performUpdate }"></slot>

        <div v-if="!performingUpdate">
            <button
                type="button"
                class="btn btn-warning"
                v-if="!askToProcessUpdate"
                @click.prevent="triggerAskToProcessUpdate"
            >
                <svg
                    v-if="!isOutdated"
                    xmlns="http://www.w3.org/2000/svg"
                    class="icon ms-1"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                </svg>
                <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="icon ms-1"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                    <path d="M7 11l5 5l5 -5"></path>
                    <path d="M12 4l0 12"></path>
                </svg>
                <span v-if="isOutdated">Download & Install Update</span>
                <span v-else>Re-install The Latest Version</span>
            </button>

            <button type="button" class="btn btn-danger" v-if="askToProcessUpdate" @click="performUpdate">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="icon ms-1"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M5 12l5 5l10 -10"></path>
                </svg>
                Click To Confirm!
            </button>
        </div>

        <div class="updating" v-if="performingUpdate">
            <div class="updating-wrapper">
                <div class="updating-container">
                    <div class="loader" v-if="loading">
                        <half-circle-spinner :animation-duration="1000" :size="32" />
                    </div>

                    <div class="percent" v-text="`${percent}%`"></div>

                    <div class="information">
                        <p
                            v-for="result in results"
                            v-text="result.text"
                            :class="result.error ? 'bold text-pink red-shadow' : 'bold'"
                        ></p>
                    </div>

                    <div class="important red-shadow" v-if="loading">
                        <strong>DO NOT CLOSE WINDOWS DURING UPDATE!</strong>
                    </div>

                    <div v-else>
                        <div class="btn btn-info" @click="refresh">Click Here To Exit</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { HalfCircleSpinner } from 'epic-spinners'

export default {
    components: {
        HalfCircleSpinner,
    },

    props: {
        updateUrl: String,
        updateId: String,
        version: String,
        firstStep: String,
        firstStepMessage: String,
        lastStep: String,
        isOutdated: Boolean,
        isActivated: Boolean,
    },

    data() {
        return {
            askToProcessUpdate: false,
            performingUpdate: false,
            results: [],
            realPercent: 0,
            percent: 0,
            percentInterval: 0,
            step: this.firstStep,
            loading: false,
        }
    },

    watch: {
        realPercent() {
            if (this.percentInterval) {
                return
            }

            this.percentInterval = setInterval(() => {
                if (this.percent >= this.realPercent) {
                    return
                }

                if (this.percent === 100) {
                    clearInterval(this.percentInterval)
                    return
                }

                this.percent += 1
            }, 100)
        },
    },

    methods: {
        triggerAskToProcessUpdate() {
            if (this.isActivated) {
                this.askToProcessUpdate = true
            } else {
                $('#system-updater-confirm-modal').modal('show')
            }
        },

        async performUpdate() {
            this.loading = true
            this.performingUpdate = true
            this.realPercent = 5

            this.results.push({ text: this.firstStepMessage, error: false })

            try {
                await this.triggerUpdate()

                setTimeout(() => this.refresh(), 30000)
            } catch (e) {
                this.loading = false
            }
        },

        async triggerUpdate() {
            return this.$httpClient
                .makeWithoutErrorHandler()
                .post(this.updateUrl, {
                    step_name: this.step,
                    update_id: this.updateId,
                    version: this.version,
                })
                .then(({ data }) => {
                    if (!data.data || !data.data.next_step || !data.data.next_message) {
                        throw new Error('Something went wrong, could not update the system.')
                    }

                    this.step = data.data.next_step
                    this.realPercent = data.data.current_percent
                    this.results.push({ text: data.data.next_message, error: false })

                    if (data.data.next_step !== this.lastStep) {
                        return this.triggerUpdate()
                    }

                    this.percent = 100
                    this.loading = false
                    clearInterval(this.percentInterval)

                    localStorage.removeItem('system_update_has_new_version')
                    localStorage.removeItem('system_update_message')
                    localStorage.removeItem('system_update_check_time')
                })
                .catch((error) => {
                    this.loading = false

                    const message = this.extractErrorMessage(error)

                    this.results.push({ text: message, error: true })

                    throw error
                })
        },

        decodeHtmlEntities(text) {
            if (typeof text !== 'string' || !text) {
                return text
            }

            const textarea = document.createElement('textarea')
            textarea.innerHTML = text
            return textarea.value
        },

        extractErrorMessage(error) {
            const stepLabels = {
                download: 'downloading update files',
                update_files: 'updating system files',
                update_database: 'updating databases',
                publish_core_assets: 'publishing core assets',
                publish_packages_assets: 'publishing packages assets',
                clean_up: 'cleaning up',
            }

            const stepLabel = stepLabels[this.step] || this.step
            const statusCode = error.response?.status

            // Try to extract message from JSON response
            const serverMessage = this.decodeHtmlEntities(
                error.response?.data?.message || error.data?.message || null
            )

            if (serverMessage) {
                return `Error while ${stepLabel}: ${serverMessage}`
            }

            // Try to extract message from HTML error page
            if (typeof error.response?.data === 'string' && error.response.data.includes('<title>')) {
                const titleMatch = error.response.data.match(/<title>(.*?)<\/title>/i)

                if (titleMatch && titleMatch[1]) {
                    const title = titleMatch[1].trim()

                    return `Error while ${stepLabel}: ${title} (HTTP ${statusCode})`
                }
            }

            // Provide a more descriptive fallback based on status code
            if (statusCode === 500) {
                return `Server error while ${stepLabel} (HTTP 500). Check the server logs for more details.`
            }

            if (statusCode === 504 || statusCode === 408) {
                return `Request timed out while ${stepLabel} (HTTP ${statusCode}). Try increasing max_execution_time or memory_limit.`
            }

            if (statusCode === 503) {
                return `Service unavailable while ${stepLabel} (HTTP 503). The server may be overloaded.`
            }

            if (statusCode) {
                return `Error while ${stepLabel}: ${error.message} (HTTP ${statusCode})`
            }

            // Network error (no response)
            if (!error.response) {
                return `Network error while ${stepLabel}. Please check your connection and try again.`
            }

            return error.message || 'An unknown error occurred.'
        },

        refresh() {
            window.location.reload()
        },
    },
}
</script>

<style lang="scss" scoped>
.updating {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    overflow: hidden;
    backdrop-filter: blur(5px);

    > .updating-wrapper {
        position: absolute;
        top: calc(30% - 100px);
        height: 100%;
        width: 100%;

        > .updating-container {
            max-width: 500px;
            margin: 0 auto;
            text-align: center;

            .loader {
                margin: 0 auto;
                &::after {
                    display: none;
                }
            }

            .percent {
                font-size: 86px;
                color: #fefefe;
                font-family:
                    ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
                margin-bottom: 24px;
            }

            .information {
                padding: 0 8px;
                margin: 32px 0;
                font-size: 18px;
                color: #efefef;
            }

            .important {
                color: #efefef;
            }

            .loader {
                .half-circle-spinner {
                    margin: 0 auto 20px;
                }
            }
        }
    }

    .red {
        color: #fdc9c9;
    }

    .red-shadow {
        text-shadow: 0 0 16px #ef0012;
    }
}
</style>
