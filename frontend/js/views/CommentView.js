export class CommentView {
  constructor() {
    this.initEventListeners();
  }

  init() {
    this.initEventListeners();
  }

  initEventListeners() {
    document.querySelectorAll(".btn-edit-comment").forEach((button) => {
      button.addEventListener("click", (e) => this.handleEdit(e));
    });
  }

  handleEdit(event) {
    const container = event.target.closest(".comment-item");
    const commentId = event.target.dataset.id;
    const contentDiv = container.querySelector(".comment-content");
    const contentP = contentDiv.querySelector("p");

    const oldContent = contentP.textContent;

    contentDiv.textContent = "";

    const textarea = document.createElement("textarea");
    textarea.className = "edit-textarea";
    textarea.value = oldContent;

    const saveBtn = document.createElement("button");
    saveBtn.className = "btn-save";
    saveBtn.textContent = "Enregistrer";

    contentDiv.appendChild(textarea);
    contentDiv.appendChild(saveBtn);

    saveBtn.addEventListener("click", () => {
      const newContent = textarea.value;
      this.saveEdit(commentId, newContent);
    });
  }

  async saveEdit(commentId, content) {
    try {
      const response = await fetch("/comments/edit", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ comment_id: commentId, content: content }),
      });

      const result = await response.json();
      if (result.success) {
        window.location.reload();
      } else {
        alert("Erreur lors de la modification.");
      }
    } catch (error) {
      console.error("Erreur:", error);
    }
  }
}
