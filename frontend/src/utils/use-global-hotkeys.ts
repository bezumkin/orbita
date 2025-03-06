import { onMounted, onUnmounted } from 'vue'

export function useGlobalHotkeys(player: any) {
    // Check if the focus is on an input element
    function isInputFocused() {
        const activeElement = document.activeElement
        const inputElements = ['INPUT', 'TEXTAREA', 'SELECT', 'BUTTON']
        return activeElement && (
            inputElements.includes(activeElement.tagName) ||
            activeElement.getAttribute('contenteditable') === 'true' ||
            activeElement.closest('.editorjs') !== null
        )
    }

    // Keydown event handler
    function handleKeyDown(event: KeyboardEvent) {
        if (isInputFocused() || !player.value) return

        const key = event.key.toLowerCase()

        // Prevent default browser behavior for player control keys
        if ([' ', 'k', 'f', 'm', 'arrowleft', 'arrowright', 'j', 'l'].includes(key)) {
            event.preventDefault()
        }

        if (key === ' ' || key === 'k') {
            player.value.remoteControl.togglePaused()
        } else if (key === 'f') {
            player.value.remoteControl.toggleFullscreen()
        } else if (key === 'm') {
            player.value.remoteControl.toggleMuted()
        } else if (key === 'arrowleft' || key === 'j') {
            player.value.remoteControl.seek(Math.max(0, player.value.currentTime - 10))
        } else if (key === 'arrowright' || key === 'l') {
            player.value.remoteControl.seek(Math.min(player.value.duration, player.value.currentTime + 10))
        }
    }

    onMounted(() => {
        window.addEventListener('keydown', handleKeyDown)
    })

    onUnmounted(() => {
        window.removeEventListener('keydown', handleKeyDown)
    })
}