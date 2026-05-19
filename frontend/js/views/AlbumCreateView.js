// js/views/AlbumCreateView.js

export class AlbumCreateView {
  constructor() {
    this.titleField = document.querySelector(".js-title-field");
    this.descField = document.querySelector(".js-desc-field");

    this.titleCounter = document.querySelector(".js-title-counter");
    this.descCounter = document.querySelector(".js-desc-counter");
  }

  init() {
    if (this.titleField && this.titleCounter) {
      const maxTitle =
        parseInt(this.titleField.getAttribute("maxlength")) || 50;

      this.titleField.addEventListener("input", () => {
        this.updateCounter(this.titleField, this.titleCounter, maxTitle);
      });

      this.updateCounter(this.titleField, this.titleCounter, maxTitle);
    }

    if (this.descField && this.descCounter) {
      const maxDesc = parseInt(this.descField.getAttribute("maxlength")) || 500;

      this.descField.addEventListener("input", () => {
        this.updateCounter(this.descField, this.descCounter, maxDesc);
      });

      this.updateCounter(this.descField, this.descCounter, maxDesc);
    }
  }

  updateCounter(field, counter, maxLength) {
    const currentLength = field.value.length;

    counter.textContent = `${currentLength} / ${maxLength}`;

    if (currentLength >= maxLength * 0.9) {
      counter.classList.add("counter-warning");
    } else {
      counter.classList.remove("counter-warning");
    }
  }
}
