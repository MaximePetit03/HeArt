import { PhotoModel } from "../models/PhotoModel.js";
import { PhotoView } from "../views/PhotoView.js";

export const PhotoController = {
  init() {
    document.querySelectorAll(".btn-toggle-visibility").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        const button = e.currentTarget;
        const photoId = button.dataset.photoId;

        try {
          const result = await PhotoModel.toggleVisibility(photoId);
          if (result.success) {
            const isNowVisible = button
              .querySelector("i")
              .classList.contains("fa-eye-slash");
            PhotoView.toggleIcon(button, isNowVisible);
          }
        } catch (error) {
          console.error("Erreur lors de la mise à jour :", error);
        }
      });
    });

    document.querySelectorAll(".btn-delete-photo").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        const button = e.currentTarget;
        const photoId = button.dataset.photoId;
        const photoCard = button.closest(".photo-card");

        if (!confirm("Supprimer cette photo ?")) return;

        try {
          const result = await PhotoModel.deletePhoto(photoId);
          if (result.success) {
            photoCard.remove();
          } else {
            alert("Erreur lors de la suppression.");
          }
        } catch (error) {
          console.error("Erreur :", error);
        }
      });
    });
  },
};
