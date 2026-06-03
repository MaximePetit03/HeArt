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
    this.accessModal = document.querySelector(".access-modal");
    this.btnManageAccess = document.getElementById("btn-manage-access");
    this.btnCopyLink = document.getElementById("btn-copy-link");
    this.shareContainer = document.getElementById("share-container");
    this.shareLinkInput = document.getElementById("share-link-input");
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

    const closeAccessModalButton = document.querySelector(
      ".js-close-access-modal",
    );
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

    if (this.btnCopyLink) {
      this.btnCopyLink.addEventListener("click", () => this.copyShareLink());
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
    this.tagListContainer.innerHTML = "";

    allTags.forEach((tag) => {
      const label = document.createElement("label");
      label.className = "tag-btn";

      const radio = document.createElement("input");
      radio.type = "radio";
      radio.name = "photo-tag-selection";
      radio.value = tag.id;

      radio.addEventListener("change", () => {
        if (radio.checked) {
          this.toggleTag(tag.id, photoId);
          this.updateTagsOnImage(photoId, tag.id, tag.name, true);
        }
      });

      label.appendChild(radio);
      label.appendChild(document.createTextNode(tag.name));
      this.tagListContainer.appendChild(label);
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

    tagsContainer.textContent = "";

    if (isActive) {
      const tagBadge = document.createElement("span");
      tagBadge.className = "photo-tag-badge";
      tagBadge.dataset.tagId = tagId;
      tagBadge.textContent = tagName;
      tagsContainer.appendChild(tagBadge);
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
    const isRestricted = visibility === "restricted";

    if (this.btnManageAccess) {
      this.btnManageAccess.style.display =
        visibility === "restricted" ? "inline-block" : "none";
    }

    if (this.shareContainer) {
      isRestricted
        ? this.shareContainer.classList.remove("hidden")
        : this.shareContainer.classList.add("hidden");
    }
  }

  async openAccessModal() {
    this.accessModal.showModal();
    const modalContent = document.querySelector(".js-access-modal-content");
    modalContent.innerHTML = "<p>Chargement des données...</p>";

    try {
      const [guests, users] = await this.fetchAccessData();
      modalContent.textContent = "";

      this.renderGuestList(guests, modalContent);
      this.renderAddUserForm(users, modalContent);
    } catch (error) {
      console.error("Erreur :", error);
      modalContent.textContent = "Erreur lors du chargement des accès.";
    }
  }

  async fetchAccessData() {
    const [guestsResponse, usersResponse] = await Promise.all([
      fetch(`/albums/getInvitations?id=${this.albumId}&t=${Date.now()}`),
      fetch("/users/list"),
    ]);
    return [await guestsResponse.json(), await usersResponse.json()];
  }

  renderGuestList(guests, container) {
    const currentUserId = parseInt(this.pageContainer.dataset.currentUserId);
    const title = document.createElement("h4");
    title.textContent = "Invités actuels";
    container.appendChild(title);

    const list = document.createElement("ul");
    if (guests.length === 0) {
      list.innerHTML = "<li>Aucun invité</li>";
    } else {
      guests.forEach((guest) => {
        const item = document.createElement("li");
        item.textContent = guest.username + " ";

        if (parseInt(guest.id) !== currentUserId) {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.textContent = "Retirer du groupe";
          btn.className = "btn-delete-small";
          btn.addEventListener("click", () => this.removeGuest(guest.id));
          item.appendChild(btn);
        } else {
          const badge = document.createElement("span");
          badge.textContent = " (Vous)";
          badge.style.fontSize = "0.8em";
          badge.style.color = "#888";
          item.appendChild(badge);
        }
        list.appendChild(item);
      });
    }
    container.appendChild(list);
  }

  renderAddUserForm(users, container) {
    const title = document.createElement("h4");
    title.textContent = "Ajouter un utilisateur";
    container.appendChild(title);

    const select = document.createElement("select");
    select.className = "js-user-select";
    select.id = "user-select";

    users.forEach((user) => {
      const option = document.createElement("option");
      option.value = user.id;
      option.textContent = user.username;
      select.appendChild(option);
    });
    container.appendChild(select);

    const btn = document.createElement("button");
    btn.type = "button";
    btn.textContent = "Ajouter";
    btn.className = "btn-primary";
    btn.addEventListener("click", () => this.addGuest());
    container.appendChild(btn);
  }

  async addGuest() {
    const userSelect = document.querySelector(".js-user-select");
    const userId = userSelect ? userSelect.value : null;

    if (!userId) {
      alert("Veuillez sélectionner un utilisateur.");
      return;
    }

    try {
      const response = await fetch("/albums/inviteUser", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          album_id: this.albumId,
          user_id: parseInt(userId),
        }),
      });

      const text = await response.text();
      console.log("Réponse brute du serveur :", text);

      const result = JSON.parse(text);

      if (result.success) {
        await this.openAccessModal();
      } else {
        alert(
          "Erreur : " +
            (result.message || "Impossible d'ajouter l'utilisateur."),
        );
      }
    } catch (error) {
      console.error("Erreur complète :", error);
      alert("Détail de l'erreur : " + error.message);
    }
  }

  async removeGuest(userId) {
    try {
      const response = await fetch("/albums/removeGuest", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ album_id: this.albumId, user_id: userId }),
      });

      const text = await response.text();
      console.log("Réponse brute du serveur :", text);

      const result = JSON.parse(text);

      if (result.success) {
        await this.openAccessModal();
      } else {
        alert("Erreur serveur : " + (result.message || "Erreur inconnue"));
      }
    } catch (error) {
      console.error("Erreur détaillée :", error);
      alert("Détail de l'erreur : " + error.message);
    }
  }

  async copyShareLink() {
    try {
      const response = await fetch("/albums/get-share-link", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ albumId: this.albumId }),
      });

      const data = await response.json();

      if (data.link) {
        await navigator.clipboard.writeText(data.link);

        const originalHTML = this.btnCopyLink.innerHTML;
        this.btnCopyLink.innerHTML =
          '<i class="fa-solid fa-check"></i> Copié !';

        setTimeout(() => {
          this.btnCopyLink.innerHTML = originalHTML;
        }, 2000);
      } else {
        throw new Error("Lien introuvable");
      }
    } catch (error) {
      console.error("Erreur lors de la copie :", error);
      alert("Impossible de copier le lien.");
    }
  }
}
