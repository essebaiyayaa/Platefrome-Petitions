
let lastSeenPetitionId = 0;

function showToast(title, message) {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) return;
    
    const toastId = 'toast-' + Date.now();
    
    const toastHTML = `
        <div id="${toastId}" class="toast custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-bell me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = document.getElementById(toastId);
    
    const toast = new bootstrap.Toast(toastElement, { 
        autohide: false
    });
    
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}
function loadTopPetition() {
    const xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success && response.petition) {
                        const titleEl = document.getElementById('topPetitionTitle');
                        const countEl = document.getElementById('topPetitionCount');
                        const bannerEl = document.getElementById('topPetitionBanner');
                        
                        if (titleEl && countEl && bannerEl) {
                            titleEl.textContent = response.petition.titre;
                            countEl.textContent = response.petition.signatures;
                            bannerEl.style.display = 'flex';
                        }
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON (loadTopPetition):', e);
                }
            } else {
                console.error('Erreur AJAX loadTopPetition:', xhr.status);
            }
        }
    };
    
    xhr.open('GET', 'get_top_petition.php', true);
    xhr.send();
}
function checkNewPetitions() {
    const xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success && response.hasNew) {
                        const petition = response.petition;
                        
                        console.log('Nouvelle pétition détectée: ID ' + petition.id);
                        showToast(
                            'Nouvelle Pétition !',
                            '<strong>' + escapeHtml(petition.titre) + '</strong><br>' +
                            '<small>Par ' + escapeHtml(petition.nomPorteur) + '</small><br>' +
                            '<em class="text-muted" style="font-size: 0.85rem;">La page va se recharger dans 5 secondes...</em>'
                        );
                        lastSeenPetitionId = petition.id;
                        setTimeout(function() {
                            location.reload();
                        }, 5000);
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON (checkNewPetitions):', e);
                }
            } else {
                console.error('Erreur AJAX checkNewPetitions:', xhr.status);
            }
        }
    };
    
    xhr.open('GET', 'check_new_petitions.php?last_id=' + lastSeenPetitionId, true);
    xhr.send();
}
function initPetitionList(initialLastId) {
    lastSeenPetitionId = initialLastId;
    console.log('Système AJAX initialisé avec polling optimisé');
    console.log('Dernier ID pétition vu: ' + lastSeenPetitionId);
    loadTopPetition();
    checkNewPetitions();
    setInterval(checkNewPetitions, 3000);
    setInterval(loadTopPetition, 30000);
}
function initSignatureForm() {
    const form = document.getElementById('signatureForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                e.preventDefault();
                alert('Veuillez compléter le CAPTCHA avant de continuer.');
                return false;
            }
        });
    }
}
function loadRecentSignatures(petitionId) {
    const container = document.getElementById('recentSignaturesContainer');
    if (!container) return;
    
    const xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success && response.signatures.length > 0) {
                        let html = '';
                        
                        response.signatures.forEach(function(signature) {
                            const initial = signature.PrenomS.charAt(0).toUpperCase();
                            const date = new Date(signature.DateS);
                            const dateFormatted = date.toLocaleDateString('fr-FR', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            
                            html += `
                                <div class="signature-item">
                                    <div class="signature-info">
                                        <div class="signature-avatar">${initial}</div>
                                        <div class="signature-details">
                                            <div class="signature-name">
                                                ${signature.PrenomS} ${signature.NomS}
                                            </div>
                                            <div class="signature-location">
                                                <i class="fas fa-map-marker-alt me-1"></i>${signature.PaysS}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="signature-date">
                                        ${dateFormatted}
                                    </div>
                                </div>
                            `;
                        });
                        
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<div class="no-signatures"><i class="fas fa-inbox fa-2x mb-2"></i><p>Aucune signature pour le moment. Soyez le premier à signer !</p></div>';
                    }
                } catch (e) {
                    container.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des signatures</div>';
                }
            } else {
                container.innerHTML = '<div class="alert alert-danger">Erreur de connexion au serveur</div>';
            }
        }
    };
    
    xhr.open('GET', 'get_recent_signatures.php?id=' + petitionId, true);
    xhr.send();
}

function initSignaturePage(petitionId) {
    initSignatureForm();
    loadRecentSignatures(petitionId);
    setInterval(function() {
        loadRecentSignatures(petitionId);
    }, 10000);
}
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}