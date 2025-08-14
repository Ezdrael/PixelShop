<div class="content-card">
    <div class="form-header">
        <h2><?php echo $this->title; ?></h2>
        <a href="<?php echo BASE_URL; ?>/albums/view/<?php echo $album['id']; ?>" class="action-btn" title="Повернутися до альбому"><i class="fas fa-arrow-left"></i></a>
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        
        <div class="form-body">
            <div id="file-upload-zone" class="file-upload-zone">
                <i class="fas fa-cloud-upload-alt"></i>
                <p><strong>Перетягніть файли сюди</strong> або натисніть, щоб обрати</p>
                <input type="file" id="photos-input" name="photos[]" multiple accept="image/jpeg, image/png, image/gif, image/webp">
            </div>
            
            <div id="preview-container" class="preview-container">
                </div>

            <div class="form-group-inline" style="margin-top: 1.5rem;">
                <div class="form-control-wrapper">
                    <button type="submit" class="btn-primary"><i class="fas fa-upload"></i> Завантажити обрані фото</button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
/* Нові стилі для зони завантаження та попереднього перегляду */
.file-upload-zone {
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    position: relative;
    transition: background-color 0.2s;
}
.file-upload-zone:hover {
    background-color: var(--sidebar-hover-bg);
}
.file-upload-zone i {
    font-size: 3rem;
    color: var(--accent-color);
}
.file-upload-zone p {
    margin-top: 1rem;
    color: var(--secondary-text);
}
.file-upload-zone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}
.preview-item {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.5rem;
    position: relative;
}
.preview-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
}
.preview-item .note-input {
    width: 100%;
    margin-top: 0.5rem;
    padding: 0.5rem;
    font-size: 0.9em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const photosInput = document.getElementById('photos-input');
    const previewContainer = document.getElementById('preview-container');

    photosInput.addEventListener('change', () => {
        previewContainer.innerHTML = ''; // Очищуємо контейнер при новому виборі
        const files = photosInput.files;

        if (!files.length) {
            return;
        }

        // Перебираємо обрані файли
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Створюємо FileReader для читання файлу
            const reader = new FileReader();
            
            // Ця функція спрацює, коли файл буде повністю прочитано
            reader.onload = (event) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';

                // Створюємо HTML для мініатюри та поля вводу примітки
                previewItem.innerHTML = `
                    <img src="${event.target.result}" alt="${file.name}">
                    <input type="text" name="notes[${i}]" class="form-control note-input" placeholder="Примітка (необов'язково)...">
                `;
                
                previewContainer.appendChild(previewItem);
            };
            
            // Починаємо читати файл. Результат буде доступний в reader.onload
            reader.readAsDataURL(file);
        }
    });
});
</script>