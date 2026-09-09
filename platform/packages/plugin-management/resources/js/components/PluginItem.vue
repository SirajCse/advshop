<script>
import { defineComponent } from 'vue'

export default defineComponent({
    props: {
        plugin: {
            type: Object,
            required: true,
        },
    },

    emits: ['showPlugin', 'install', 'uninstall', 'toggleActivation'],

    data() {
        return {
            isInstalled: false,
            isActivated: false,
        }
    },

    mounted() {
        this.checkInstalled()
        this.checkActivated()
        this.initTooltips()

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
        checkInstalled() {
            this.isInstalled = window.marketplace.installed.includes(this.packageName)
        },
        checkActivated() {
            this.isActivated = window.marketplace.activated.includes(this.packageName)
        },
        initTooltips() {
            this.$nextTick(() => {
                $(this.$el).find('[data-bs-toggle="tooltip"]').tooltip({ placement: 'top' })
            })
        },
    },

    computed: {
        packageName() {
            const packageName = this.plugin.package_name

            return packageName.substring(packageName.indexOf('/') + 1)
        },
        isVerifiedAuthor() {
            return this.plugin.author_name?.toLowerCase().includes('botble')
        },
    },
})
</script>

<template>
    <div class="col-md-3">
        <div class="card h-100">
            <div
                class="img-responsive img-responsive-21x9 card-img-top"
                :style="{ backgroundImage: `url(${plugin.image_url})` }"
            ></div>

            <div class="card-body">
                <h3 class="card-title d-flex align-items-center gap-1">
                    {{ plugin.name }}
                    <svg
                        v-if="isVerifiedAuthor"
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="#1d9bf0"
                        class="flex-shrink-0"
                        data-bs-toggle="tooltip"
                        title="Verified by Botble"
                    >
                        <path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.36-6.2 6.77z"/>
                    </svg>
                </h3>
                <p class="text-secondary">{{ plugin.description }}</p>
            </div>

            <div class="card-footer">
                <div class="d-flex">
                    <a
                        v-if="!isInstalled && plugin.price > 0"
                        :href="plugin.buy_url"
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
                        v-if="!isInstalled && !(plugin.price > 0)"
                        type="button"
                        class="btn btn-primary"
                        @click="$emit('install', $event, plugin.id)"
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
                            class="btn"
                            :class="{
                                'btn-danger': isActivated,
                                'btn-primary': !isActivated,
                            }"
                            @click="$emit('toggle-activation', $event, packageName)"
                            v-text="isActivated ? __('base.deactivate') : __('base.activate')"
                        />
                    </template>
                    <button class="btn ms-auto" @click="$emit('showPlugin', plugin)">
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
                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                            <path d="M12 9h.01"></path>
                            <path d="M11 12h1v4h1"></path>
                        </svg>
                        {{ __('base.detail') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
