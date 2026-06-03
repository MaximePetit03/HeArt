export class AlbumView {
  constructor() {
    this.filterContainer = document.querySelector(".js-filter-tags");
    this.photosGrid = document.querySelector(".js-photos-grid");
    this.photoItems = document.querySelectorAll(".js-photo-item");
    this.btnLeave = document.querySelector(".btn-leave");
  }

  init() {
    if (!this.filterContainer || !this.photosGrid) return;

    this.initFilter();

    if (this.btnLeave) {
      this.initLeaveAlbum();
    }
  }

  initFilter() {
    this.filterContainer.addEventListener("change", (e) => {
      const selectedTagId = e.target.value;

      this.photoItems.forEach((item) => {
        const itemTags = item.dataset.tags ? item.dataset.tags.split(",") : [];

        if (selectedTagId === "all" || itemTags.includes(selectedTagId)) {
          item.style.display = "";
          item.classList.remove("hidden");
        } else {
          item.style.display = "none";
          item.classList.add("hidden");
        }
      });
    });
  }

  initLeaveAlbum() {
    this.btnLeave.addEventListener("click", async () => {
      console.log("Clic détecté sur le bouton quitter !");

      if (!confirm("Êtes-vous sûr de vouloir quitter cet album ?")) return;

      const albumId = this.btnLeave.dataset.albumId;

      try {
        const response = await fetch("/albums/leave", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ album_id: albumId }),
        });

        const result = await response.json();
        if (result.success) {
          window.location.href = "/albums/my-albums";
        } else {
          alert("Erreur : " + (result.message || "Impossible de quitter"));
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
        alert("Une erreur est survenue.");
      }
    });
  }
}
