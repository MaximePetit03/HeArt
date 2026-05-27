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
    // 1. On cible l'article précis du commentaire cliqué
    const container = event.target.closest(".comment-item");
    const commentId = event.target.dataset.id;
    const contentDiv = container.querySelector(".comment-content");
    const contentP = contentDiv.querySelector("p");

    const oldContent = contentP.innerText;

    contentDiv.innerHTML = `
        <textarea class="edit-textarea">${oldContent}</textarea>
        <button class="btn-save">Enregistrer</button>
    `;

    container.querySelector(".btn-save").addEventListener("click", () => {
      const newContent = container.querySelector(".edit-textarea").value;
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
