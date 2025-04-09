document.addEventListener("DOMContentLoaded", () => {
    const profilePicInput = document.getElementById("profile-pic");
    const profileNameInput = document.getElementById("profile-name");
    const profileBioInput = document.getElementById("profile-bio");
    const profileForm = document.getElementById("profile-form");

    const profilePicPreview = document.getElementById("profile-pic-preview");
    const profileNamePreview = document.getElementById("profile-name-preview");
    const profileBioPreview = document.getElementById("profile-bio-preview");

    // Update profile picture preview
    profilePicInput.addEventListener("change", (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                profilePicPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Update name and bio preview in real-time
    profileNameInput.addEventListener("input", () => {
        profileNamePreview.textContent = profileNameInput.value || "Your Name";
    });

    profileBioInput.addEventListener("input", () => {
        profileBioPreview.textContent = profileBioInput.value || "Your bio will appear here...";
    });

    // Handle form submission
    profileForm.addEventListener("submit", (event) => {
        event.preventDefault();
        alert("Profile updated successfully!");
    });
});