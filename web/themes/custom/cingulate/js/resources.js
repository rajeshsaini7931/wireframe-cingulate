/**
 * @file
 * Resource Materials behaviors — tab switching for cards.
 */

(function (Drupal, once) {
  "use strict";

  /**
   * Resource cards tab switching behavior.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes resource card click handlers.
   */
  Drupal.behaviors.cingulateResources = {
    attach(context) {
      const cards = once("cingulateResourceCards", ".resource-card", context);
      const itemsContainer = context.querySelector("#resources-items");

      if (!cards.length || !itemsContainer) {
        return;
      }

      cards.forEach((card) => {
        const clickHandler = (e) => {
          e.preventDefault();

          // Deactivate all cards
          cards.forEach((c) => {
            c.classList.remove("resource-card--active");
            c.setAttribute("aria-expanded", "false");
          });

          // Activate clicked card
          card.classList.add("resource-card--active");
          card.setAttribute("aria-expanded", "true");

          // Show items container
          if (!itemsContainer.classList.contains("is-open")) {
            itemsContainer.classList.add("is-open");
          }
        };

        // Click handler
        card.addEventListener("click", clickHandler);

        // Keyboard accessibility
        card.addEventListener("keydown", (e) => {
          if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            clickHandler(e);
          }
        });
      });
    },

    detach(context, settings, trigger) {
      if (trigger === "unload") {
        once.remove("cingulateResourceCards", ".resource-card", context);
      }
    },
  };

  /**
   * Resource item video player behavior.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes play button handlers for resource items.
   */
  Drupal.behaviors.cingulateResourcePlayer = {
    attach(context) {
      const playButtons = once(
        "cingulateResourcePlay",
        ".resource-item__play",
        context,
      );

      if (!playButtons.length) {
        return;
      }

      playButtons.forEach((button) => {
        button.addEventListener("click", (e) => {
          e.preventDefault();

          // Get resource item data
          const item = button.closest(".resource-item");
          const videoUrl = item.getAttribute("data-video-url");
          const title = item.getAttribute("data-title");
          const topic = item.getAttribute("data-topic");

          // Extract Vimeo ID from URL
          const vimeoId = videoUrl.split("/").pop();

          // Update modal content
          const playerTitle = document.getElementById("playerTitle");
          const playerTopic = document.getElementById("playerTopic");
          const videoContainer = document.getElementById(
            "videoPlayerContainer",
          );

          if (playerTitle) playerTitle.textContent = title;
          if (playerTopic) playerTopic.textContent = topic;

          // Inject Vimeo iframe
          if (videoContainer) {
            videoContainer.innerHTML = `
              <iframe
                src="https://player.vimeo.com/video/${vimeoId}?autoplay=1"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; border-radius: 8px;"
                allow="autoplay; fullscreen; picture-in-picture"
                allowfullscreen
                title="${title}"
              ></iframe>
            `;
          }

          // Open Bootstrap modal
          const modal = document.getElementById("resourcePlayerModal");
          if (modal && typeof bootstrap !== "undefined") {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
          }
        });
      });

      // Handle modal close - clear video to stop playback
      const modal = document.getElementById("resourcePlayerModal");
      if (modal) {
        modal.addEventListener("hidden.bs.modal", () => {
          const videoContainer = document.getElementById(
            "videoPlayerContainer",
          );
          if (videoContainer) {
            videoContainer.innerHTML = "";
          }
        });
      }

      // Handle transcript toggle chevron rotation
      const transcriptToggle = context.querySelector(
        ".resources__player-transcript-toggle",
      );
      const transcriptChevron = context.querySelector(
        ".resources__player-transcript-chevron",
      );

      if (transcriptToggle && transcriptChevron) {
        transcriptToggle.addEventListener("click", () => {
          transcriptChevron.classList.toggle("is-closed");
        });
      }
    },

    detach(context, settings, trigger) {
      if (trigger === "unload") {
        once.remove("cingulateResourcePlay", ".resource-item__play", context);
      }
    },
  };
})(Drupal, once);
