export const PhotoModel = {
  async toggleVisibility(photoId) {
    const response = await fetch("/photos/toggle-visibility", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ photoId: photoId }),
    });

    if (!response.ok) throw new Error("Erreur serveur");
    return await response.json();
  },

  async deletePhoto(photoId) {
    const response = await fetch("/photos/delete", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ photoId: photoId }),
    });

    if (!response.ok) throw new Error("Erreur serveur");
    return await response.json();
  },
};
