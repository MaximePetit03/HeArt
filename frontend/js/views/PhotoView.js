export class PhotoView {
  constructor(containerSelector, itemSelector) {
    this.container = document.querySelector(containerSelector);
    this.itemSelector = itemSelector;

    if (this.container) {
      this.init();
    }
  }

  init() {
    this.container.addEventListener("change", (e) => {
      this.applyFilter(e.target.value);
    });
  }

  applyFilter(tagId) {
    const items = document.querySelectorAll(this.itemSelector);

    items.forEach((item) => {
      const tags = item.dataset.tags ? item.dataset.tags.split(",") : [];

      if (tagId === "all" || tags.includes(tagId)) {
        item.style.display = "";
        item.classList.remove("hidden");
      } else {
        item.style.display = "none";
        item.classList.add("hidden");
      }
    });
  }

  static toggleIcon(buttonElement, isVisible) {
    const icon = buttonElement.querySelector("i");
    if (!icon) return;
    icon.classList.toggle("fa-eye", isVisible);
    icon.classList.toggle("fa-eye-slash", !isVisible);
  }
}
