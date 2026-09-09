class HistoryManager {
    constructor(state, maxLength = 20) {
        this.state = state
        this.stack = []
        this.pointer = -1
        this.maxLength = maxLength
        this.isLocked = false
    }

    init(initialData) {
        this.stack = [JSON.parse(JSON.stringify(initialData))]
        this.pointer = 0
    }

    push(data) {
        if (this.isLocked) return

        if (this.pointer < this.stack.length - 1) {
            this.stack = this.stack.slice(0, this.pointer + 1)
        }

        this.stack.push(JSON.parse(JSON.stringify(data)))

        if (this.stack.length > this.maxLength) {
            this.stack.shift()
        } else {
            this.pointer++
        }

        this.state.emit('history-changed', {
            canUndo: this.canUndo(),
            canRedo: this.canRedo()
        })
    }

    undo() {
        if (!this.canUndo()) return null

        this.isLocked = true
        this.pointer--
        const data = JSON.parse(JSON.stringify(this.stack[this.pointer]))
        this.isLocked = false

        this.state.emit('history-changed', {
            canUndo: this.canUndo(),
            canRedo: this.canRedo()
        })

        return data
    }

    redo() {
        if (!this.canRedo()) return null

        this.isLocked = true
        this.pointer++
        const data = JSON.parse(JSON.stringify(this.stack[this.pointer]))
        this.isLocked = false

        this.state.emit('history-changed', {
            canUndo: this.canUndo(),
            canRedo: this.canRedo()
        })

        return data
    }

    canUndo() {
        return this.pointer > 0
    }

    canRedo() {
        return this.pointer < this.stack.length - 1
    }
}

window.HistoryManager = HistoryManager
