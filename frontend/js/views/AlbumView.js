export class AlbumView {
  constructor() {
    this.filterContainer = document.querySelector(".js-filter-tags");
    this.albumsGrid = document.querySelector(".js-albums-grid");
    this.albumCards = document.querySelectorAll(".js-album-card");
  }

  init() {
    if (!this.filterContainer || !this.albumsGrid) return;

    this.initFilter();
  }

  initFilter() {
    this.filterContainer.addEventListener("change", (e) => {
      const selectedTagId = e.target.value;
      console.log("Tag sélectionné :", selectedTagId);

      this.albumCards.forEach((card) => {
        const albumTags = card.dataset.tags ? card.dataset.tags.split(",") : [];
        console.log("Tags de la carte :", albumTags);

        if (selectedTagId === "all" || albumTags.includes(selectedTagId)) {
          card.style.display = "";
          card.classList.remove("hidden");
        } else {
          card.style.display = "none";
          card.classList.add("hidden");
        }
      });
    });
  }
}
