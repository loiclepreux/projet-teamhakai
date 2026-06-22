// Lorsque tout le contenu HTML est entièrement chargé, on exécute ce bloc.
document.addEventListener("DOMContentLoaded", () => {
    // On récupère le chemin de la page actuelle pour afficher un message dans la console.
    const pathname = window.location.pathname;
    console.log("✅ JS chargé pour la page :", pathname);


// ==============================
// === Nav-barre uniquement ==
// ==============================
// ============================
// 🍔 Gestion du menu burger
// ============================

    const burger = document.querySelector(".menuburger"); // Sélectionne l'icône du menu burger.
    const menu = document.getElementById("menu1"); // Sélectionne le menu à afficher/cacher.


// Si les éléments existent dans le DOM.
    if (burger && menu) {
      // On clic sur le bouton burger.
        burger.addEventListener("click", () => {
           // On ajoute ou enlève la classe "show" pour afficher ou cacher le menu.
        menu.classList.toggle("show");
        // On empêche ou rétablit le scroll de la page selon que le menu est visible ou non.
        document.body.style.overflow = menu.classList.contains("show") ? "hidden" : "auto";
        });
    }

// ======================================
// 🟡 Positionnement de l'animation balle
// ======================================

    const balle = document.querySelector(".animation-balle"); // Sélectionne l'élément animé.
    // Si l'élément existe et qu'on est sur une page spécifique, on ajuste sa position verticale
    if (balle) {
        balle.style.top = "5.5%";
        // Position par défaut de la balle ("5.5%").
    }

// ============================
// ✨ Animation des liens
// ============================

    const liens = document.querySelectorAll("#menu1 a"); // Sélectionne tous les liens du menu.
    // Fonction utilitaire : détecte si deux éléments se "touchent" (collision)
    function detecteCollision(rect1, rect2) {
      const marge = 10; // Marge d'imprécision pour lisser la détection
      return (
        rect1.left < rect2.right + marge &&
        rect1.right > rect2.left - marge &&
        rect1.top < rect2.bottom + marge &&
        rect1.bottom > rect2.top - marge
      );
    }
  
    // Si la balle et les liens existent.
    if (balle && liens.length > 0) {
      // On configure le style de la balle pour qu'elle soit par-dessus mais non interactive.
      balle.style.position = "fixed";
      balle.style.zIndex = "0";
      balle.style.pointerEvents = "none";
  
      // Ensemble pour garder en mémoire quels liens ont déjà été touchés (évite répétition).
      const liensTouchés = new Set();
  
      // Vérifie les collisions toutes les 100 ms.
      setInterval(() => {
        const balleRect = balle.getBoundingClientRect(); // Position et taille actuelle de la balle.

        // Pour chaque lien du menu.
        liens.forEach((lien) => {
          const lienRect = lien.getBoundingClientRect(); // Position du lien.
          const collisionDétectée = detecteCollision(balleRect, lienRect); // Collision ou non.
  
          // Si une collision est détectée et que ce lien n'a pas encore été touché.
          if (collisionDétectée && !liensTouchés.has(lien)) {
            lien.classList.add("flash"); // Ajoute un effet visuel.
            liensTouchés.add(lien);  // Marque le lien comme déjà touché.

            // Après 500 ms, on retire l'effet et on permet un nouveau déclenchement.
            setTimeout(() => {
              lien.classList.remove("flash");
              liensTouchés.delete(lien);
            }, 500);
          }
        });
      }, 100); // Vérifie les collisions toutes les 0.1 seconde.
    }

// ==============================
// === Page Accueil uniquement ==
// ==============================
// ======================
// 🎞️ Carrousel d'images
// ======================

      const images = document.querySelectorAll(".cod");
      let index = 0;

      if (images.length > 0) {
        images[index].classList.add("active"); // Affiche la première image.
        setInterval(() => {
          // Masquer image actuelle.
          images[index].classList.remove("active");
          images[index].style.visibility = "hidden";

          // Passer à l'image suivante (ou revenir à 0)
          index = (index + 1) % images.length;

          // Afficher nouvelle image.
          images[index].style.visibility = "visible";
          images[index].classList.add("active");
        }, 3000);  // toutes les 3 secondes.
      }
  
// ===========================
// 👤 Connexion / Inscription
// ===========================

      // Bouton "se connecter" et sa section.
      const btnConnexion = document.querySelector(".seconnecter");
      const sectionConnexion = document.getElementById("connexion-section");
  
      if (btnConnexion && sectionConnexion) {
        // Toggle (ouvrir/fermer) section de connexion au clic.
        btnConnexion.addEventListener("click", () => {
          sectionConnexion.classList.toggle("active");
          menu.classList.remove("show"); // Ferme le menu burger si ouvert.
        });

        // Fermer la section si on clique en dehors.
        document.addEventListener("click", (e) => {
          if (!sectionConnexion.contains(e.target) && e.target !== btnConnexion) {
            sectionConnexion.classList.remove("active");
          }
        });
      }
  
      // Gestion du formulaire d'inscription.
      const showSignup = document.getElementById("show-signup");
      const signupContainer = document.querySelector(".form-container");
      const closeSignup = document.querySelector(".close-btn");
  
      if (showSignup && signupContainer && closeSignup) {
        // Ouvrir le formulaire d’inscription au clic.
        showSignup.addEventListener("click", (e) => {
          e.preventDefault();
          signupContainer.classList.add("active");
        });

        // Fermer le formulaire d’inscription.
        closeSignup.addEventListener("click", () => {
          signupContainer.classList.remove("active");
        });

        // Fermer si on clique en dehors du formulaire.
        document.addEventListener("click", (e) => {
          if (!signupContainer.contains(e.target) && e.target !== showSignup) {
            signupContainer.classList.remove("active");
          }
        });
      }
    
  
// ============================
// 📚 Affichage des membres ==
// ============================

    if (pathname.endsWith("/php/membre.php") && typeof membres !== "undefined") {

      const template = document.getElementById("membre-template"); // Récupère le <template> HTML.
      const grid = document.querySelector(".membre-grid"); // Zone où les cartes seront ajoutées.
  
      // Parcours la liste des membres récupérés (généralement en JSON).
      membres.forEach((membre, index) => {
        if (!membre.pseudo) return; // Ignore les entrées sans pseudo.

        // Clone le contenu du template pour chaque membre.
        const clone = template.content.cloneNode(true);
        
         // Remplissage des données dans la carte membre.
        clone.querySelector(".pseudo span").textContent = membre.pseudo;
        clone.querySelector(".photo img").src = membre.photo_profil ? "profils/" + membre.photo_profil : "profils/default.jpg"; // Photo personnalisée ou image par défaut.
        clone.querySelector(".style span").textContent = membre.style_jeu;
        clone.querySelector(".age span").textContent = membre.age;
        clone.querySelector(".genre span").textContent = membre.genre;
        clone.querySelector(".profil span").textContent = membre.biographie;


// ===================================
// ⚙️ Bouton "modifier mon profil" ==
// ===================================

        if (typeof currentUser !== "undefined" && currentUser === membre.pseudo) {
        //  Si c'est l'utilisateur connecté → affiche le bouton de modification  //
        const boutonModifier = document.createElement("a");
        boutonModifier.href = `modifier_profils.php?id=${membre.id}`;
        boutonModifier.classList.add("edit-profil-icon");
        boutonModifier.title = "Modifier mon profil";
        boutonModifier.innerHTML = `<i class="fas fa-cog"></i>`; // Icône FontAwesome (roue dentée).

        // On insère la roue dentée dans le bloc pseudo.
        clone.querySelector(".pseudo").appendChild(boutonModifier);
      }


// ====================================
// ❌ Bouton de suppression (admin) ==
// ====================================

        if (currentUserRole === 'admin') {
          // Si l'utilisateur est administrateur, on ajoute un formulaire de suppression.
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = 'action_admin_membre.php';
          form.classList.add('delete-cross-form');

          // Confirmation avant envoi du formulaire.
          form.onsubmit = () => confirm('❌ Supprimer ce membre ?');

          form.innerHTML = `
            <input type="hidden" name="csrf_token" value="${csrfToken}">
            <input type="hidden" name="id_membre" value="${membre.id}">
            <input type="hidden" name="action" value="supprimer">
            <button type="submit" class="delete-cross" title="Supprimer">✖</button>
          `;

          // Ajout de la croix de suppression dans le coin de la carte.
          clone.querySelector('.membre').appendChild(form);
        }
  
        // Insertion finale de la carte du membre dans la grille.
        grid.appendChild(clone);

        // Animation d'apparition progressive (effet de décalage entre chaque carte).
        const card = grid.lastElementChild;
        if (index < 50 && card) {
          setTimeout(() => card.classList.add("show"), index * 100); // délai en ms croissant.
        }
      });
    }
  

// ===========================
// 🎬 Page Bibliothèque  ====
// ===========================

    if (pathname.includes("bibliotheque.php")) {
      console.log("BIBLIOTHEQUE JS OK");
    
      const template = document.getElementById("bibliotheque-template")?.content; // Template HTML pour une carte vidéo.
      const grid = document.querySelector(".bibliotheque-grid"); // Grille d’affichage des vidéos.
      const toggleFormBtn = document.getElementById("toggle-form"); // Bouton pour afficher/masquer le formulaire d'ajout.
      const formSection = document.getElementById("video-form"); // Section contenant le formulaire d'ajout de vidéo.

      // Si des vidéos sont disponibles et que le template + la grille existent.
      if (typeof videos !== "undefined" && template && grid) {
        videos.forEach(video => {
          const clone = template.cloneNode(true); // On clone le contenu HTML du template.
          const videoContainer = clone.querySelector(".video");

          // Détection du type de vidéo : fichier direct ou lien YouTube.
          const isDirectVideo = video.url.match(/\.(mp4|webm|ogg)$/i);
          const isYouTube = video.url.includes("youtube.com") || video.url.includes("youtu.be");
        
          // insertion vidéo.
          if (isDirectVideo) {
            // Si c’est un fichier vidéo direct.
            const vid = document.createElement("video");
            vid.src = video.url;
            vid.controls = true;
            videoContainer.appendChild(vid);

          } else if (isYouTube) {
            // Si c’est une vidéo YouTube, on extrait l’ID de la vidéo.
            let videoId;
            if (video.url.includes("youtube.com")) {
              const urlParams = new URLSearchParams(new URL(video.url).search);
              videoId = urlParams.get("v");
            } else {
              videoId = video.url.split("/").pop().split("?")[0];
            }

            // Création de l’iframe YouTube.
            const iframe = document.createElement("iframe");
            iframe.src = `https://www.youtube.com/embed/${videoId}`;
            iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
            iframe.allowFullscreen = true;
            iframe.frameBorder = "0";
            iframe.style.width = "100%";
            iframe.style.height = "100%";
            videoContainer.appendChild(iframe);
          }
        
          // 🧾 Mise à jour des infos textuelles de la carte.
          clone.querySelector(".pseudo p").textContent = video.pseudo || "Inconnu";
          clone.querySelector(".date p").textContent = new Date(video.date_publication).toLocaleDateString("fr-FR");

          const checkboxContainer = clone.querySelector(".select-checkbox");
          const checkbox = clone.querySelector(".delete-checkbox");

          // ✅ Afficher la case à cocher si l'utilisateur connecté est l'auteur ou admin.
          if ((currentUser && currentUser === video.pseudo) || currentUserRole === "admin") {
          checkboxContainer.style.display = "block";
        }

          // 🔁 Quand on coche → on met à jour le champ caché du formulaire avec l'URL.
          checkbox?.addEventListener("change", () => {
            if (checkbox.checked) {
              document.getElementById("video-url").value = video.url;
            }
      });

          // ❤️ Gestion du système de likes.
          const likeContainer = clone.querySelector(".like-container");
          likeContainer.dataset.id = video.id;
          likeContainer.querySelector(".like-count").textContent = video.likes;
        
          // Ajout de la carte à la grille.
          grid.appendChild(clone);

        
// ======================
// 🔁 Like vidéo  ======
// ======================

        const videoId = likeContainer.dataset.id;
        const icon = likeContainer.querySelector(".like-icon");
        const count = likeContainer.querySelector(".like-count");
        const storageKey = `liked_${videoId}`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || ''; // Protection CSRF récupéré depuis le script PHP.

        // Si déjà liké → coloration de l’icône (pré-remplissage).
        if (localStorage.getItem(storageKey)) {
          icon.style.color = "#ff004c";
        }

        // Clic sur l'icône de like
        likeContainer.addEventListener("click", () => {
          // Si déjà liké → ignore le clic.
          if (localStorage.getItem(storageKey)) return;

          const formData = new FormData();
          formData.append("video_id", videoId);
          formData.append("csrf_token", csrfToken);

          console.log("Envoi du like pour vidéo", videoId, "avec CSRF token :", csrfToken);

          fetch("like_video.php", {
            method: "POST",
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === "success") {
               // Mise à jour du compteur de likes.
              count.textContent = data.likes;
              icon.style.color = "#ff004c";
              localStorage.setItem(storageKey, true); // Empêche nouveau like.
            } else {
              console.warn("Erreur lors du like :", data.message);
            }
          })
          .catch(err => console.error("Erreur fetch like :", err));
        });
        });
      }

      // 🎛️ Bouton pour afficher/masquer le formulaire vidéo.
      if (toggleFormBtn && formSection) {
        toggleFormBtn.addEventListener("click", (e) => {
          e.preventDefault();
          console.log("🟢 Bouton 'Ajouter / Supprimer' cliqué");
          formSection.classList.toggle("active");
        });
      }
    
       // ❌ Bouton pour fermer le formulaire.
      const closeFormBtn = formSection?.querySelector(".close-btn");
      if (closeFormBtn) {
        closeFormBtn.addEventListener("click", () => {
          console.log("🔴 Bouton 'Fermer' cliqué");
          formSection.classList.remove("active");
        });
      }
    }
  

// =======================
// 🛒 Page Boutique  ====
// =======================

    if (pathname.endsWith("/php/boutique.php")) {

      const CART_KEY = 'hakai_panier';
      const panier = JSON.parse(localStorage.getItem(CART_KEY) || '{}');

      // Sélections des éléments du DOM.
      const notif = document.querySelector(".notif"); // Pastille de notification sur l'icône panier.
      const buttonsAdd = document.querySelectorAll(".add-btn"); // Boutons pour ajouter un article.
      const buttonsRemove = document.querySelectorAll(".remove-btn"); // Boutons pour retirer un article.
      const panierTable = document.getElementById("panier-table"); // Tableau affichant les articles du panier.
      const totalPriceElement = document.getElementById("total-price"); // Élément où s'affiche le prix total.
      const panierIcon = document.querySelector(".panier"); // Icône du panier à cliquer pour ouvrir le bon de commande.
      const bonDeCommande = document.getElementById("bon-de-commande"); // Fenêtre pop-up du panier.
      const closeBtn = document.querySelector(".close-btn"); // Bouton pour fermer la fenêtre du panier.
      const form = document.getElementById("commande-form"); // Formulaire d'envoi de commande.

      // ✅ Affiche un message temporaire (vert ou rouge) en haut à droite de l'écran.
      function showMessage(text, color) {
        const message = document.createElement("div");
        message.className = "confirmation-message";
        message.textContent = text;
        Object.assign(message.style, {
          background: color,
        });
  
        document.body.appendChild(message);

        // Disparition progressive du message.
        setTimeout(() => {
          message.style.opacity = "0";
          setTimeout(() => message.remove(), 500);
        }, 2000);
    }

    // 🔄 Met à jour la pastille de notification sur l’icône panier.
    function updateNotif() {
        const total = Object.values(panier).reduce((acc, item) => acc + item.quantite, 0);
        notif.textContent = total;
        notif.style.opacity = total > 0 ? "1" : "0";
        localStorage.setItem(CART_KEY, JSON.stringify(panier));
    }

    // 🧾 Met à jour le tableau affichant les articles du panier.
    function updateTable() {
        panierTable.innerHTML = "";
        let total = 0;


        Object.values(panier).forEach(item => {
            const row = document.createElement("tr");
            const sousTotal = item.prix * item.quantite;

            // Génère une ligne du tableau avec des inputs cachés pour le formulaire.
            row.innerHTML = `
            <td>${item.nom}<input type="hidden" name="produits[]" value="${item.nom}"></td>
            <td>${item.quantite}<input type="hidden" name="quantites[]" value="${item.quantite}"></td>
            <td>${sousTotal.toFixed(2)} €<input type="hidden" name="montants[]" value="${sousTotal}"></td>`;

            panierTable.appendChild(row);
            total += sousTotal;
        });

        totalPriceElement.textContent = total.toFixed(2);
    }

    // ➕ Gestion du clic sur un bouton "ajouter".
    buttonsAdd.forEach(button => {
        button.addEventListener("click", () => {
            const id = button.dataset.id;
            const nom = button.dataset.nom;
            const prix = parseFloat(button.dataset.prix);
            const parent = button.closest(".actions");
            const input = parent?.querySelector(".chiffre");
            const quantity = parseInt(input?.value || "1", 10); // Valeur par défaut : 1.

         // Si l'article n'est pas encore dans le panier, on l'ajoute.
        if (!panier[id]) panier[id] = { nom, quantite: 0, prix };

        // On ajoute la quantité choisie.
        panier[id].quantite += quantity;

            updateNotif();
            updateTable();
            showMessage("Article ajouté", "#4CAF50");
        });
    });

    // ➖ Gestion du clic sur un bouton "supprimer".
    buttonsRemove.forEach(button => {
        button.addEventListener("click", () => {
            const id = button.dataset.id;
            const parent = button.closest(".actions");
            const input = parent?.querySelector(".chiffre");
            const quantity = parseInt(input?.value || "1", 10);

        if (panier[id]) {
            panier[id].quantite = Math.max(0, panier[id].quantite - quantity); // Empêche valeur négative.
            if (panier[id].quantite === 0) delete panier[id]; // Supprime l'article s'il n'en reste plus.
        }

            updateNotif();
            updateTable();
            showMessage("Article supprimé", "#FF4C4C");
        });
    });

    // 🛒 Clic sur l'icône du panier.
    panierIcon?.addEventListener("click", () => {
        if (Object.keys(panier).length === 0) {
            showMessage("Votre panier est vide", "#FF4C4C");
            return;
        }

        bonDeCommande.style.display = "block";
        bonDeCommande.style.opacity = "0";

        setTimeout(() => {
            bonDeCommande.style.opacity = "1";
            bonDeCommande.style.transition = "opacity 0.3s ease";
        }, 10);
    });

    // ❌ Fermer le bon de commande.
    closeBtn?.addEventListener("click", () => {
        bonDeCommande.style.opacity = "0";
        setTimeout(() => bonDeCommande.style.display = "none", 300);
    });

    // 📤 Envoi du formulaire de commande.
    form?.addEventListener("submit", (e) => {
    const total = parseFloat(totalPriceElement.textContent);

    if (isNaN(total) || total <= 0) {
        e.preventDefault();
        showMessage("Votre panier est vide", "#FF4C4C");
    } else {
        localStorage.removeItem(CART_KEY);
    }
});

    // Restaure l'affichage si un panier était sauvegardé.
    if (Object.keys(panier).length > 0) {
        updateNotif();
        updateTable();
    }
    }
});
