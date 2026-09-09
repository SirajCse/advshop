import { bootstrap, tabler } from '@tabler/core'

globalThis.bootstrap = bootstrap
globalThis.tabler = tabler

import setupProgress from './base/progress'

setupProgress({
    showSpinner: true,
})
