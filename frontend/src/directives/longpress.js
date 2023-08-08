export default {
    mounted(el, binding) {
        let pressTimer = null;
        const duration = parseInt(binding.arg) || 500; // Default long-press duration of 500ms (can be customized using v-longpress:duration="handler")

        const startPress = (event) => {

            if (!pressTimer) {
                // Add a class to the element when the long-press event is triggered
                el.classList.add('longpress');
                pressTimer = setTimeout(() => {
                    binding.value(); // Trigger the provided function when the long-press duration is over
                    el.classList.remove('longpress'); // Remove the class from the element when the long-press event is over
                    pressTimer = null;
                    clearTimeout(pressTimer);
                }, duration);

            }
        };

        const cancelPress = () => {
            if (pressTimer) {
                clearTimeout(pressTimer);
                pressTimer = null;
                // Remove the class from the element when the long-press event is cancelled
                el.classList.remove('longpress');
            }
        };

        el.addEventListener('mousedown', startPress);
        el.addEventListener('touchstart', startPress);

        el.addEventListener('click', cancelPress);
        el.addEventListener('mouseup', cancelPress);
        el.addEventListener('touchend', cancelPress);
        el.addEventListener('touchcancel', cancelPress);
    },
};
