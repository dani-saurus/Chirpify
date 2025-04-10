// Wait for the DOM to load
document.addEventListener("DOMContentLoaded", () => {
    const postForm = document.getElementById("post-form");
    const tweetInput = document.getElementById("tweet-input");
    const imageInput = document.getElementById("image-input");
    const uploadButton = document.getElementById("upload-button");
    const postButton = document.getElementById("post-button");
    const tweetsContainer = document.getElementById("tweets-container");

    // Initialize event listeners for existing tweets
    document.querySelectorAll('.tweet').forEach(tweet => {
        addTweetListeners(tweet);
    });

    // Trigger file input when the custom button is clicked
    uploadButton.addEventListener("click", () => {
        imageInput.click();
    });

    // Add event listener to the Post button
    postForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const tweetText = tweetInput.value.trim();
        const imageFile = imageInput.files[0];

        if (!tweetText && !imageFile) {
            alert("Please enter a message or upload an image before posting!");
            return;
        }

        try {
            const formData = new FormData(postForm);
            
            const response = await fetch('api/create_post.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                tweetInput.value = '';
                imageInput.value = '';
                window.location.reload();
            } else {
                throw new Error(data.error || 'Failed to create post');
            }
        } catch (error) {
            console.error('Error details:', error);
            alert('Error creating post: ' + error.message);
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
        const postId = tweetElement.dataset.postId;
        const likeButton = tweetElement.querySelector(".like-button");
        const commentButton = tweetElement.querySelector(".comment-button");
        const likeCountElement = tweetElement.querySelector(".like-count");
        const commentCountElement = tweetElement.querySelector(".comment-count");
        const commentsContainer = tweetElement.querySelector(".comments-container");

        // Like button functionality
        likeButton.addEventListener("click", async () => {
            try {
                const formData = new FormData();
                formData.append('post_id', postId);
                
                const response = await fetch('api/like_post.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Update UI based on server response
                    if (data.action === 'liked') {
                        likeButton.textContent = "Liked";
                        likeButton.classList.add("liked");
                    } else {
                        likeButton.textContent = "Like";
                        likeButton.classList.remove("liked");
                    }
                    likeCountElement.textContent = `${data.like_count} Likes`;
                } else {
                    throw new Error(data.error || 'Failed to process like');
                }
            } catch (error) {
                console.error('Error liking post:', error);
                alert('Error liking post: ' + error.message);
            }
        });

        // Comment button functionality
        commentButton.addEventListener("click", async () => {
            const commentText = prompt("Enter your comment:");
            if (commentText && commentText.trim()) {
                try {
                    const formData = new FormData();
                    formData.append('post_id', postId);
                    formData.append('content', commentText.trim());
                    
                    const response = await fetch('api/add_comment.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Create and append the new comment element
                        const commentElement = document.createElement("div");
                        commentElement.classList.add("comment");
                        commentElement.innerHTML = `
                            <span class="comment-username">${data.username}</span>
                            <p>${data.content}</p>
                        `;
                        commentsContainer.appendChild(commentElement);
                        
                        // Update comment count
                        commentCountElement.textContent = `${data.comment_count} Comments`;
                    } else {
                        throw new Error(data.error || 'Failed to add comment');
                    }
                } catch (error) {
                    console.error('Error adding comment:', error);
                    alert('Error adding comment: ' + error.message);
                }
            }
        });
    }
});