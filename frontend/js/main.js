import { AlbumCreateView } from "./views/AlbumCreateView.js";
import { AlbumEditView } from "./views/AlbumEditView.js";
import { PhotoController } from "./controllers/PhotoController.js";
import { PhotoView } from "./views/PhotoView.js";
import { AlbumView } from "./views/AlbumView.js";
import { CommentView } from "./views/CommentView.js";

document.addEventListener("DOMContentLoaded", () => {
  console.log("HeArt Application Initialized");

  if (document.querySelector(".js-albums-grid")) {
    const albumView = new AlbumView();
    albumView.init();
  }

  if (document.querySelector(".js-title-field")) {
    const albumCreateView = new AlbumCreateView();
    albumCreateView.init();
  }

  if (document.querySelector(".js-edit-album-page")) {
    const albumEditView = new AlbumEditView();
    albumEditView.init();
    PhotoController.init();
  }

  if (document.querySelector(".comments-section")) {
    const commentView = new CommentView();
    commentView.init();
  }

  if (document.querySelector(".js-filter-tags")) {
    new PhotoView(".js-filter-tags", ".js-photo-item");
  }
});
