document.addEventListener('DOMContentLoaded', function() {
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const profilePhotoInput = document.getElementById('profilePhotoInput');
    
    // Gestione cambio foto profilo
    changePhotoBtn.addEventListener('click', function() {
        profilePhotoInput.click();
    });
    
    profilePhotoInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            if (file.size > 2 * 1024 * 1024) { // 2MB
                alert('La dimensione massima consentita è 2MB');
                return;
            }
            
            const formData = new FormData();
            formData.append('profile_picture', file);
            
            fetch('../api/upload_profile_picture.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Aggiorna l'immagine visualizzata
                    const profileImg = document.querySelector('.profile-picture img');
                    profileImg.src = '../uploads/' + data.filename + '?t=' + new Date().getTime();
                    alert('Foto profilo aggiornata con successo!');
                } else {
                    alert('Errore: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante il caricamento');
            });
        }
    });
});