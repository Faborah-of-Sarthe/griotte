export default {
    mounted(el, binding) {
      let pressTimer = null;
      const duration = parseInt(binding.arg) || 500; // Default long-press duration of 500ms (can be customized using v-longpress:duration="handler")
  
      const startPress = () => {
        if (!pressTimer) {
          pressTimer = setTimeout(() => {
            binding.value(); // Trigger the provided function when the long-press duration is over
          }, duration);
        }
      };
  
      const cancelPress = () => {
        if (pressTimer) {
          clearTimeout(pressTimer);
          pressTimer = null;
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
  