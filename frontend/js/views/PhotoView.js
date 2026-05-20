export const PhotoView = {
  toggleIcon(buttonElement, isVisible) {
    const icon = buttonElement.querySelector("i");
    if (isVisible) {
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    } else {
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    }
  },
};
