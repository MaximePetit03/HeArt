import { PhotoModel } from "../models/PhotoModel.js";
import { PhotoView } from "../views/PhotoView.js";

export const PhotoController = {
  init() {
    document.querySelectorAll(".js-toggle-visibility").forEach((btn) => {
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
  },
};
