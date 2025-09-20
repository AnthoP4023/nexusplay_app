<?php
function getGameViewJavaScript($message = '', $message_type = '', $user_logged_in = false) {
    ob_start();
    ?>
    <script>
        class GameViewFunctions {
            constructor() {
                this.starInputs = null;
                this.starLabels = null;
                this.reviewContainer = null;
                this.toggleButton = null;
                this.init();
            }

            init() {
                document.addEventListener('DOMContentLoaded', () => {
                    this.initializeElements();
                    this.setupEventListeners();
                    this.setupStarRating();
                    this.handlePostSubmissionActions();
                });
            }

            initializeElements() {
                this.starInputs = document.querySelectorAll('.star-rating input[type="radio"]');
                this.starLabels = document.querySelectorAll('.star-rating label');
                this.reviewContainer = document.getElementById('reviewFormContainer');
                this.toggleButton = document.getElementById('toggleReviewForm');
            }

            setupEventListeners() {
                if (this.toggleButton) {
                    this.toggleButton.addEventListener('click', () => this.toggleReviewForm());
                }
            }

            toggleReviewForm() {
                if (!this.reviewContainer || !this.toggleButton) return;
                
                if (this.reviewContainer.style.display === 'none' || this.reviewContainer.style.display === '') {
                    this.showReviewForm();
                } else {
                    this.hideReviewForm();
                }
            }

            showReviewForm() {
                this.reviewContainer.style.display = 'block';
                this.toggleButton.innerHTML = '<i class="fas fa-times"></i> Cancelar';
                this.scrollToForm();
            }

            hideReviewForm() {
                this.reviewContainer.style.display = 'none';
                this.toggleButton.innerHTML = '<i class="fas fa-plus"></i> Escribir reseña';
            }

            scrollToForm() {
                this.reviewContainer.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }

            setupStarRating() {
                if (!this.starLabels.length) return;

                this.starLabels.forEach((label, index) => {
                    label.addEventListener('mouseenter', () => {
                        this.highlightStars(index);
                    });

                    label.addEventListener('mouseleave', () => {
                        this.restoreStarState();
                    });

                    label.addEventListener('click', () => {
                        setTimeout(() => {
                            this.updateStarSelection();
                        }, 10);
                    });
                });

                this.updateStarSelection();
            }

            highlightStars(index) {
                this.starLabels.forEach((label, i) => {
                    label.style.color = i <= index ? '#fbbf24' : '#666';
                });
            }

            restoreStarState() {
                const checkedStar = document.querySelector('.star-rating input[type="radio"]:checked');
                if (checkedStar) {
                    const checkedIndex = Array.from(this.starInputs).indexOf(checkedStar);
                    this.highlightStars(checkedIndex);
                } else {
                    this.resetStars();
                }
            }

            updateStarSelection() {
                const checkedStar = document.querySelector('.star-rating input[type="radio"]:checked');
                if (checkedStar) {
                    const checkedIndex = Array.from(this.starInputs).indexOf(checkedStar);
                    this.highlightStars(checkedIndex);
                }
            }

            resetStars() {
                this.starLabels.forEach(label => {
                    label.style.color = '#666';
                });
            }

            clearReviewForm() {
                const textarea = document.getElementById('comentario');
                if (textarea) {
                    textarea.value = '';
                }
                
                this.starInputs.forEach(input => {
                    input.checked = false;
                });
                
                this.resetStars();
            }

            handlePostSubmissionActions() {
                <?php if (!empty($message) && $message_type === 'success' && $user_logged_in): ?>
                    setTimeout(() => {
                        this.clearReviewForm();
                    }, 1000);
                <?php endif; ?>

                <?php if (!empty($message) && $user_logged_in): ?>
                    this.showReviewForm();
                <?php endif; ?>
            }

            static showMessage(type, message) {
                const messageEl = document.getElementById('reviewMessage');
                if (!messageEl) return;

                messageEl.className = `review-message ${type}`;
                messageEl.style.display = 'block';
                
                const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                messageEl.innerHTML = `<i class="fas ${iconClass}"></i> ${message}`;
                
                messageEl.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }

            static validateReviewForm(formData) {
                const puntuacion = formData.get('puntuacion');
                const comentario = formData.get('comentario')?.trim();

                if (!puntuacion || puntuacion < 1 || puntuacion > 5) {
                    GameViewFunctions.showMessage('error', 'Por favor selecciona una puntuación de 1 a 5 estrellas.');
                    return false;
                }

                if (!comentario || comentario.length < 10) {
                    GameViewFunctions.showMessage('error', 'Por favor escribe un comentario de al menos 10 caracteres.');
                    return false;
                }

                if (comentario.length > 500) {
                    GameViewFunctions.showMessage('error', 'El comentario no puede exceder 500 caracteres.');
                    return false;
                }

                return true;
            }
        }

        window.gameViewInstance = new GameViewFunctions();
        window.GameViewFunctions = GameViewFunctions;
    </script>
    <?php
    return ob_get_clean();
}

function generateStars($rating) {
    $stars = '';
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5 ? 1 : 0;
    $empty_stars = 5 - $full_stars - $half_star;
    
    for ($i = 0; $i < $full_stars; $i++) {
        $stars .= '<i class="fas fa-star"></i>';
    }
    if ($half_star) {
        $stars .= '<i class="fas fa-star-half-alt"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $stars .= '<i class="far fa-star"></i>';
    }
    
    return $stars;
}

function validateGameReview($puntuacion, $comentario) {
    $errors = [];
    
    if (empty($puntuacion) || $puntuacion < 1 || $puntuacion > 5) {
        $errors[] = "La puntuación debe ser entre 1 y 5 estrellas.";
    }
    
    if (empty(trim($comentario))) {
        $errors[] = "El comentario es obligatorio.";
    } elseif (strlen(trim($comentario)) < 10) {
        $errors[] = "El comentario debe tener al menos 10 caracteres.";
    } elseif (strlen(trim($comentario)) > 500) {
        $errors[] = "El comentario no puede exceder 500 caracteres.";
    }
    
    return $errors;
}

function formatGamePrice($price) {
    return '$' . number_format($price, 2);
}

function formatReviewDate($date) {
    return date('d/m/Y', strtotime($date));
}

function getGameImagePath($image) {
    return 'images/juegos/' . ($image ?: 'default.jpg');
}

function getUserAvatarPath($avatar) {
    return 'images/users/' . ($avatar ?: 'default-avatar.png');
}
?>