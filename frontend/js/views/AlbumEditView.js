export class AlbumEditView {
  constructor() {
    this.pageContainer = document.querySelector(".js-edit-album-page");
    this.dropZone = document.querySelector(".js-upload-dropzone");
    this.fileInput = document.querySelector(".js-photo-upload-input");
    this.photosGrid = document.querySelector(".js-photos-grid");
  }

  init() {
    if (
      !this.pageContainer ||
      !this.dropZone ||
      !this.fileInput ||
      !this.photosGrid
    )
      return;

    this.albumId = this.pageContainer.dataset.albumId;

    this.initEvents();
  }

  initEvents() {
    this.dropZone.addEventListener("click", () => {
      this.fileInput.click();
    });

    this.fileInput.addEventListener("click", (e) => {
      e.stopPropagation();
    });

    this.dropZone.addEventListener("dragover", (e) => {
      e.preventDefault();
      this.dropZone.classList.add("drag-over");
    });

    this.dropZone.addEventListener("dragleave", () => {
      this.dropZone.classList.remove("drag-over");
    });

    this.dropZone.addEventListener("drop", (e) => {
      e.preventDefault();
      this.dropZone.classList.remove("drag-over");

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        this.handleUpload(files);
      }
    });

    this.fileInput.addEventListener("change", () => {
      const files = this.fileInput.files;
      if (files.length > 0) {
        this.handleUpload(files);
        this.fileInput.value = "";
      }
    });
  }

  async handleUpload(files) {
    const formData = new FormData();
    formData.append("album_id", this.albumId);

    for (let i = 0; i < files.length; i++) {
      formData.append("photos[]", files[i]);
    }

    try {
      this.dropZone.style.opacity = "0.5";

      const response = await fetch("/albums/upload-photos", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        result.photos.forEach((photo) => {
          this.addPhotoToGrid(photo.url, photo.id);
        });
      } else {
        alert(result.error || "Une erreur est survenue lors de l'upload.");
      }
    } catch (error) {
      console.error("Erreur réseau :", error);
      alert("Impossible de joindre le serveur.");
    } finally {
      this.dropZone.style.opacity = "1";
    }
  }

  addPhotoToGrid(photoUrl, photoId) {
    const card = document.createElement("div");
    card.className = "photo-card";
    card.dataset.photoId = photoId;

    card.innerHTML = `
            <img src="${photoUrl}" alt="Photo de l'album" loading="lazy">
            <button type="button" class="btn-delete-photo js-delete-photo" data-photo-id="${photoId}" aria-label="Supprimer cette photo">
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
            </button>
        `;

    this.photosGrid.insertBefore(card, this.photosGrid.firstChild);
  }
}
