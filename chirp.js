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

        if (tweetText || imageFile) {
            const tweetElement = document.createElement("div");
            tweetElement.classList.add("tweet");

            let tweetContent = `
                <div class="pfp">PFP</div>
                <div>
                    <span style="font-weight: bold;">Your Name</span>
                    <p class="tweet-text">${tweetText}</p>
            `;

            if (imageFile) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    tweetContent += `
                        <div class="tweet-image">
                            <img src="${e.target.result}" alt="Uploaded Image" style="max-width: 100%; border-radius: 10px; margin-top: 10px;">
                        </div>
                    `;
                    appendTweet(tweetElement, tweetContent);
                };
                reader.readAsDataURL(imageFile);
            } else {
                appendTweet(tweetElement, tweetContent);
            }

            tweetInput.value = "";
            imageInput.value = "";
        } else {
            alert("Please enter a message or upload an image before posting!");
        }
    });

    // Function to append tweet content and add listeners
    function appendTweet(tweetElement, tweetContent) {
        tweetContent += `
            <div class="tweet-actions">
                <button class="like-button">Like</button>
                <span class="like-count">0 Likes</span>
                <button class="comment-button">Comment</button>
                <span class="comment-count">0 Comments</span>
            </div>
            <div class="comments-container"></div>
        </div>`;
        tweetElement.innerHTML = tweetContent;
        tweetsContainer.prepend(tweetElement);
        addTweetListeners(tweetElement);
    }

    // Function to add listeners for like and comment actions
    function addTweetListeners(tweetElement) {
        const likeButton = tweetElement.querySelector(".like-button");
        const commentButton = tweetElement.querySelector(".comment-button");
        const likeCountElement = tweetElement.querySelector(".like-count");
        const commentCountElement = tweetElement.querySelector(".comment-count");
        const commentsContainer = tweetElement.querySelector(".comments-container");

        // Like button functionality
        likeButton.addEventListener("click", () => {
            let likeCount = parseInt(likeCountElement.textContent);
            if (!likeButton.classList.contains("liked")) {
                likeButton.textContent = "Liked";
                likeButton.classList.add("liked");
                likeCount++;
            } else {
                likeButton.textContent = "Like";
                likeButton.classList.remove("liked");
                likeCount--;
            }
            likeCountElement.textContent = `${likeCount} Likes`;
        });

        // Comment button functionality
        commentButton.addEventListener("click", () => {
            const commentText = prompt("Enter your comment:");
            if (commentText && commentText.trim()) {
                const commentElement = document.createElement("div");
                commentElement.classList.add("comment");
                commentElement.textContent = commentText.trim();
                commentsContainer.appendChild(commentElement);

                // Update comment count
                let commentCount = parseInt(commentCountElement.textContent);
                commentCount++;
                commentCountElement.textContent = `${commentCount} Comments`;
            }
        });
    }
});