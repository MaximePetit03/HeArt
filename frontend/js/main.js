import { AlbumCreateView } from "./views/AlbumCreateView.js";
import { AlbumEditView } from "./views/AlbumEditView.js";
import { PhotoController } from "./controllers/PhotoController.js";

document.addEventListener("DOMContentLoaded", () => {
  console.log("HeArt Application Initialized");

  if (document.querySelector(".js-title-field")) {
    const albumCreateView = new AlbumCreateView();
    albumCreateView.init();
  }

  if (document.querySelector(".js-edit-album-page")) {
    const albumEditView = new AlbumEditView();
    albumEditView.init();
    PhotoController.init();
  }
});
