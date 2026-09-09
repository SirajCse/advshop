<script>
import { defineComponent } from 'vue'

export default defineComponent({
    props: {
        plugin: {
            type: Object,
            required: true,
        },
    },

    emits: ['back', 'install', 'uninstall', 'toggleActivation'],

    data() {
        return {
            isInstalled: false,
            isActivated: false,
        }
    },

    mounted() {
        this.initModal()
        this.checkInstalled()
        this.checkActivated()

        $event.on('plugin-installed', (packageName) => {
            if (packageName === this.packageName) {
                this.isInstalled = true
            }
        })

        $event.on('plugin-toggle-activation', (packageName) => {
            if (packageName === this.packageName) {
                this.isActivated = !this.isActivated
            }
        })

        $event.on('plugin-uninstalled', (packageName) => {
            if (packageName === this.packageName) {
                this.isInstalled = false
                this.isActivated = false
            }
        })
    },

    methods: {
        initModal() {
            const modal = new bootstrap.Modal(this.$refs.modal)

            modal.show()

            this.$refs.modal.addEventListener('hidden.bs.modal', () => {
                this.$emit('back')
            })

            this.$nextTick(() => {
                $(this.$refs.modal).find('[data-bs-toggle="tooltip"]').tooltip({ placement: 'top' })
            })
        },
        checkInstalled() {
            this.isInstalled = window.marketplace.installed.includes(this.packageName)
        },
        checkActivated() {
            this.isActivated = window.marketplace.activated.includes(this.packageName)
        },
        install() {
            bootstrap.Modal.getInstance(this.$refs.modal).hide()
            this.$emit('install', $event, this.plugin.id)
        },
    },

    computed: {
        packageName() {
            const packageName = this.plugin.package_name

            return packageName.substring(packageName.indexOf('/') + 1)
        },
        purchaseUrl() {
            // Paid plugins cannot be installed from the admin panel - redirect to the purchase/marketplace page instead
            return this.plugin.buy_url || this.plugin.url
        },
        authorAvatar() {
            return `https://github.com/${this.plugin.author_name}.png`
        },
        isVerifiedAuthor() {
            return this.plugin.author_name?.toLowerCase().includes('botble')
        },
    },
})
</script>

<template>
    <div class="modal modal-blur fade" ref="modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-3 px-5">
                    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between w-100">
                        <div>
                            <h2 class="mb-1 d-flex align-items-center gap-1">
                                {{ plugin.name }}
                                <svg
                                    v-if="isVerifiedAuthor"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="22"
                                    height="22"
                                    viewBox="0 0 24 24"
                                    fill="#1d9bf0"
                                    class="flex-shrink-0"
                                    data-bs-toggle="tooltip"
                                    title="Verified by Botble"
                                >
                                    <path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.36-6.2 6.77z"/>
                                </svg>
                            </h2>
                            <p class="text-muted mb-0">{{ plugin.description }}</p>
                        </div>

                        <a :href="plugin.url" target="_blank" class="btn me-5 d-none d-md-block">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="icon"
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
                                <path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6"></path>
                                <path d="M11 13l9 -9"></path>
                                <path d="M15 4h5v5"></path>
                            </svg>
                            {{ __('base.view_on_marketplace') }}
                        </a>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body bg-body">
                    <img :src="plugin.image_url" :alt="plugin.name" class="rounded" />

                    <div class="card my-3">
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('base.author') }}</div>
                                    <div class="datagrid-content">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="avatar avatar-xs me-2 rounded"
                                                :style="`background-image: url(${authorAvatar})`"
                                            ></span>
                                            {{ plugin.author_name }}
                                            <svg
                                                v-if="isVerifiedAuthor"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="#1d9bf0"
                                                class="ms-1 flex-shrink-0"
                                                data-bs-toggle="tooltip"
                                    title="Verified by Botble"
                                            >
                                                <path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.36-6.2 6.77z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('base.downloads') }}</div>
                                    <div class="datagrid-content">{{ plugin.downloads_count }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('base.version') }}</div>
                                    <div class="datagrid-content">{{ plugin.latest_version }}</div>
                                </div>
                                <div class="datagrid-item" v-if="plugin.version_check">
                                    <div class="datagrid-title">{{ __('base.compatible_version') }}</div>
                                    <div class="datagrid-content">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="icon text-success"
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
                                        {{ plugin.minimum_core_version }}
                                    </div>
                                </div>
                                <div class="datagrid-item" v-else>
                                    <div class="datagrid-title">{{ __('base.incompatible_version') }}</div>
                                    <div class="datagrid-content">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="icon text-danger"
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
                                        {{ plugin.minimum_core_version }}
                                    </div>
                                </div>
                                <div class="datagrid-item" v-if="plugin.ratings_count > 0">
                                    <div class="datagrid-title">{{ __('base.rating') }}</div>
                                    <div class="datagrid-content d-flex align-items-center gap-1">
                                        <div class="lh-1">
                                            <svg
                                                v-for="n in 5"
                                                :key="n"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-sm text-secondary"
                                                :class="{ 'text-yellow': n <= plugin.ratings_avg }"
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
                                                <path
                                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                                    stroke-width="0"
                                                    fill="currentColor"
                                                ></path>
                                            </svg>
                                        </div>
                                        <span class="text-muted">({{ plugin.ratings_count }})</span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('base.last_update') }}</div>
                                    <div class="datagrid-content">{{ plugin.humanized_last_updated_at }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('base.license') }}</div>
                                    <div class="datagrid-content">
                                        <a :href="plugin.license_url" target="_blank" v-if="plugin.license_url">
                                            {{ plugin.license }}
                                        </a>
                                        <template v-else>{{ plugin.license }}</template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-lg" v-if="plugin.content">
                        <div class="card-body markdown" v-html="plugin.content" />
                    </div>
                </div>
                <div class="modal-footer">
                    <a
                        v-if="!isInstalled && plugin.price > 0"
                        :href="purchaseUrl"
                        target="_blank"
                        class="btn btn-warning"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon"
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
                            <path
                                d="M3 5m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"
                            ></path>
                            <path d="M3 10l18 0"></path>
                            <path d="M7 15l.01 0"></path>
                            <path d="M11 15l2 0"></path>
                        </svg>
                        {{ __('base.buy_now') }}
                    </a>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="install"
                        v-if="!isInstalled && !(plugin.price > 0)"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon"
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
                        {{ __('base.install_now') }}
                    </button>
                    <template v-if="isInstalled">
                        <button
                            type="button"
                            class="btn btn-danger"
                            @click="$emit('uninstall', $event, this.packageName)"
                            v-if="!isActivated"
                        >
                            {{ __('base.remove') }}
                        </button>
                        <button
                            type="button"
                            class="btn"
                            :class="{
                                'btn-danger': isActivated,
                                'btn-primary': !isActivated,
                            }"
                            @click="$emit('toggleActivation', $event, this.packageName)"
                            v-text="isActivated ? __('base.deactivate') : __('base.activate')"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
