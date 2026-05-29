export class AlbumEditView {
  constructor() {
    this.pageContainer = document.querySelector(".js-edit-album-page");
    this.dropZone = document.querySelector(".js-upload-dropzone");
    this.fileInput = document.querySelector(".js-photo-upload-input");
    this.photosGrid = document.querySelector(".js-photos-grid");
    this.tagModal = document.getElementById("tag-modal");
    this.tagListContainer = document.getElementById("js-tag-list");
    this.albumVisibilitySelect = document.querySelector(
      ".js-change-album-visibility",
    );
    this.accessModal = document.getElementById("access-modal");
    this.btnManageAccess = document.getElementById("btn-manage-access");
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
    this.initVisibility();
    this.initTags();

    if (this.albumVisibilitySelect) {
      this.toggleAccessButtonVisibility(this.albumVisibilitySelect.value);
      this.albumVisibilitySelect.addEventListener("change", (e) => {
        this.toggleAccessButtonVisibility(e.target.value);
      });
    }

    const closeAccessModalButton =
      document.getElementById("close-access-modal");

    if (closeAccessModalButton) {
      closeAccessModalButton.addEventListener("click", () => {
        this.accessModal.close();
      });
    }

    if (this.btnManageAccess) {
      this.btnManageAccess.addEventListener("click", () =>
        this.openAccessModal(),
      );
    }
  }

  initVisibility() {
    if (this.albumVisibilitySelect) {
      this.albumVisibilitySelect.addEventListener("change", async (e) => {
        try {
          const response = await fetch("/albums/update-visibility", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              albumId: this.albumId,
              visibility: e.target.value,
            }),
          });
          const result = await response.json();
          if (result.success) {
            window.location.reload();
          } else {
            alert(
              "Erreur lors de la mise à jour : " +
                (result.message || "Inconnue"),
            );
          }
        } catch (error) {
          console.error("Erreur réseau :", error);
        }
      });
    }

    this.photosGrid.addEventListener("click", async (e) => {
      const toggleBtn = e.target.closest(".js-toggle-visibility");
      if (toggleBtn) {
        const photoId = toggleBtn.dataset.photoId;
        const response = await fetch("/photos/toggle-visibility", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ photoId: photoId }),
        });
        const result = await response.json();
        if (result.success) {
          const icon = toggleBtn.querySelector("i");
          icon.classList.toggle("fa-eye");
          icon.classList.toggle("fa-eye-slash");
        }
      }
    });
  }

  initEvents() {
    this.dropZone.addEventListener("click", () => this.fileInput.click());
    this.fileInput.addEventListener("click", (e) => e.stopPropagation());
    this.dropZone.addEventListener("dragover", (e) => {
      e.preventDefault();
      this.dropZone.classList.add("drag-over");
    });
    this.dropZone.addEventListener("dragleave", () =>
      this.dropZone.classList.remove("drag-over"),
    );
    this.dropZone.addEventListener("drop", (e) => {
      e.preventDefault();
      this.dropZone.classList.remove("drag-over");
      const files = e.dataTransfer.files;
      if (files.length > 0) this.handleUpload(files);
    });
    this.fileInput.addEventListener("change", () => {
      const files = this.fileInput.files;
      if (files.length > 0) {
        this.handleUpload(files);
        this.fileInput.value = "";
      }
    });
  }

  initTags() {
    this.photosGrid.addEventListener("click", (e) => {
      const tagBtn = e.target.closest(".js-open-tags");
      if (tagBtn) this.openTagModal(tagBtn.dataset.photoId);
    });
    document.getElementById("close-btn").addEventListener("click", () => {
      this.tagModal.style.display = "none";
    });
  }

  openTagModal(photoId) {
    if (!this.tagModal) return;
    this.tagModal.dataset.currentPhotoId = photoId;
    this.tagModal.style.display = "flex";
    const allTags = JSON.parse(
      document.getElementById("all-tags-data").textContent,
    );
    this.tagListContainer.textContent = "";
    allTags.forEach((tag) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "tag-btn";
      btn.textContent = tag.name;
      btn.addEventListener("click", () => {
        this.toggleTag(tag.id, photoId);
        btn.classList.toggle("tag-active");
        this.updateTagsOnImage(
          photoId,
          tag.id,
          tag.name,
          btn.classList.contains("tag-active"),
        );
      });
      this.tagListContainer.appendChild(btn);
    });
  }

  updateTagsOnImage(photoId, tagId, tagName, isActive) {
    const photoCard = document.querySelector(
      `.photo-card[data-photo-id="${photoId}"]`,
    );
    if (!photoCard) return;
    let tagsContainer = photoCard.querySelector(".photo-tags-display");
    if (!tagsContainer) {
      tagsContainer = document.createElement("div");
      tagsContainer.className = "photo-tags-display";
      photoCard.querySelector("img").after(tagsContainer);
    }
    if (isActive) {
      if (!tagsContainer.querySelector(`[data-tag-id="${tagId}"]`)) {
        const tagBadge = document.createElement("span");
        tagBadge.className = "photo-tag-badge";
        tagBadge.dataset.tagId = tagId;
        tagBadge.textContent = tagName;
        tagsContainer.appendChild(tagBadge);
      }
    } else {
      const tagBadge = tagsContainer.querySelector(`[data-tag-id="${tagId}"]`);
      if (tagBadge) tagBadge.remove();
    }
  }

  async toggleTag(tagId, photoId) {
    const response = await fetch("/photos/toggle-tag", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ photoId, tagId }),
    });
    const result = await response.json();
    if (!result.success) alert("Erreur lors de l'enregistrement du tag.");
  }

  async handleUpload(files) {
    const formData = new FormData();
    formData.append("album_id", this.albumId);
    for (let i = 0; i < files.length; i++)
      formData.append("photos[]", files[i]);
    try {
      this.dropZone.style.opacity = "0.5";
      const response = await fetch("/albums/upload-photos", {
        method: "POST",
        body: formData,
      });
      const result = await response.json();
      if (result.success) {
        result.photos.forEach((photo) =>
          this.addPhotoToGrid(photo.url, photo.id),
        );
      } else {
        alert(result.error || "Une erreur est survenue.");
      }
    } catch (error) {
      alert("Impossible de joindre le serveur.");
    } finally {
      this.dropZone.style.opacity = "1";
    }
  }

  addPhotoToGrid(photoUrl, photoId) {
    const card = document.createElement("div");
    card.className = "photo-card";
    card.dataset.photoId = photoId;
    card.innerHTML = `<img src="${photoUrl}" alt="Photo de l'album" loading="lazy">
        <div class="photo-actions">
            <button type="button" class="btn-tag js-open-tags" data-photo-id="${photoId}"><i class="fa-solid fa-tag"></i></button>
            <button type="button" class="btn-toggle-visibility js-toggle-visibility" data-photo-id="${photoId}"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
            <button type="button" class="btn-delete-photo js-delete-photo" data-photo-id="${photoId}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
        </div>`;
    this.photosGrid.insertBefore(card, this.photosGrid.firstChild);
  }

  toggleAccessButtonVisibility(visibility) {
    if (this.btnManageAccess) {
      this.btnManageAccess.style.display =
        visibility === "restricted" ? "inline-block" : "none";
    }
  }

  async openAccessModal() {
    const modalContent = document.getElementById("access-modal-content");
    modalContent.textContent = "";
    this.accessModal.showModal();

    try {
      const [guestsResponse, usersResponse] = await Promise.all([
        fetch(`/albums/getInvitations?id=${this.albumId}`),
        fetch("/users/list"),
      ]);
      const guestList = await guestsResponse.json();
      const allUsersList = await usersResponse.json();

      const currentGuestsTitle = document.createElement("h4");
      currentGuestsTitle.textContent = "Invités actuels";
      modalContent.appendChild(currentGuestsTitle);

      const guestsListContainer = document.createElement("ul");

      if (guestList.length === 0) {
        const noGuestItem = document.createElement("li");
        noGuestItem.textContent = "Aucun invité";
        guestsListContainer.appendChild(noGuestItem);
      } else {
        guestList.forEach((guest) => {
          const guestItem = document.createElement("li");
          guestItem.textContent = guest.username + " ";

          const removeGuestButton = document.createElement("button");
          removeGuestButton.textContent = "Retirer du groupe";
          removeGuestButton.addEventListener("click", () =>
            this.removeGuest(guest.id),
          );

          guestItem.appendChild(removeGuestButton);
          guestsListContainer.appendChild(guestItem);
        });
      }
      modalContent.appendChild(guestsListContainer);

      const addUserTitle = document.createElement("h4");
      addUserTitle.textContent = "Ajouter un utilisateur";
      modalContent.appendChild(addUserTitle);

      const userSelect = document.createElement("select");
      userSelect.id = "user-select";
      allUsersList.forEach((user) => {
        const userOption = document.createElement("option");
        userOption.value = user.id;
        userOption.textContent = user.username;
        userSelect.appendChild(userOption);
      });
      modalContent.appendChild(userSelect);

      const addButton = document.createElement("button");
      addButton.textContent = "Ajouter";
      addButton.className = "btn-primary";
      addButton.addEventListener("click", () => this.addGuest());
      modalContent.appendChild(addButton);
    } catch (error) {
      console.error("Détail de l'erreur :", error);
      modalContent.textContent = "Erreur lors du chargement.";
    }
  }

  async addGuest() {
    const userId = document.getElementById("user-select").value;
    await fetch("/albums/inviteUser", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ album_id: this.albumId, user_id: userId }),
    });
    this.openAccessModal();
  }

  async removeGuest(userId) {
    await fetch("/albums/removeGuest", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ album_id: this.albumId, user_id: userId }),
    });
    this.openAccessModal();
  }
}
