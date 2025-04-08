// Wait for the DOM to load
document.addEventListener("DOMContentLoaded", () => {
    const postButton = document.getElementById("post-button");
    const tweetInput = document.getElementById("tweet-input");
    const imageInput = document.getElementById("image-input");
    const uploadButton = document.getElementById("upload-button");
    const tweetsContainer = document.getElementById("tweets-container");

    // Trigger file input when the custom button is clicked
    uploadButton.addEventListener("click", () => {
        imageInput.click();
    });

    // Add event listener to the Post button
    postButton.addEventListener("click", () => {
        const tweetText = tweetInput.value.trim();
        const imageFile = imageInput.files[0];

        // Check if the input is not empty
        if (tweetText || imageFile) {
            // Create a new tweet element
            const tweetElement = document.createElement("div");
            tweetElement.classList.add("tweet");

            // Add text content
            let tweetContent = `
                <div class="pfp">pfp</div>
                <div>
                    <span style="font-weight: bold;">name</span>
                    <p>${tweetText}</p>
            `;

            // Add image if uploaded
            if (imageFile) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const imageElement = document.createElement("img");
                    imageElement.src = e.target.result;
                    imageElement.alt = "Uploaded Image";
                    imageElement.style.maxWidth = "100%";
                    imageElement.style.marginTop = "10px";

                    // Append the image to the tweet
                    tweetElement.innerHTML = tweetContent;
                    tweetElement.appendChild(imageElement);
                    tweetElement.innerHTML += `</div>`;

                    // Add the new tweet to the top of the tweets container
                    tweetsContainer.prepend(tweetElement);
                };
                reader.readAsDataURL(imageFile);
            } else {
                tweetContent += `</div>`;
                tweetElement.innerHTML = tweetContent;

                // Add the new tweet to the top of the tweets container
                tweetsContainer.prepend(tweetElement);
            }

            // Clear the input fields
            tweetInput.value = "";
            imageInput.value = "";
        } else {
            alert("Please enter a message or upload an image before posting!");
        }
    });
});