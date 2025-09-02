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

            <div id="submit-button-container" class="form-group-inline" style="margin-top: 1.5rem; display: none;">
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


.file-upload-zone { border: 2px dashed var(--border-color); /* ... */ }
.preview-container { display: grid; /* ... */ }

/* ДОДАНО: Стилі для елементів попереднього перегляду */
.preview-item {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.5rem;
    position: relative;
    display: flex;
    flex-direction: column;
}
.preview-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}
.preview-item .note-input {
    width: 100%;
    padding: 0.5rem;
    font-size: 0.9em;
    margin-top: auto; /* Притискає поле вводу до низу */
}

/* ДОДАНО: Стилі для кнопки видалення */
.preview-delete-btn {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 24px;
    height: 24px;
    background-color: var(--danger-color);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: transform 0.2s;
}
.preview-delete-btn:hover {
    transform: scale(1.1);
}
.file-upload-zone { border: 2px dashed var(--border-color); /* ... */ }
.preview-container { display: grid; /* ... */ }
.preview-item img { width: 100%; height: 150px; object-fit: cover; border-radius: 4px; margin-bottom: 0.5rem; }
.preview-item .note-input { width: 100%; padding: 0.5rem; font-size: 0.9em; margin-top: auto; }
.preview-delete-btn { position: absolute; top: -5px; right: -5px; width: 24px; height: 24px; background-color: var(--danger-color); color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 14px; font-weight: bold; display: flex; align-items: center; justify-content: center; line-height: 1; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.2s; z-index: 10; }
.preview-delete-btn:hover { transform: scale(1.1); }

/* --- ДОДАНО: Стилі для анімації --- */
.preview-item {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.5rem;
    position: relative;
    display: flex;
    flex-direction: column;
    /* 1. Плавний перехід для з'їжджання */
    transition: transform 0.3s ease, opacity 0.3s ease;
}

/* 2. Клас, що застосовується для анімації видалення */
.preview-item.is-deleting {
    transform: scale(0.5);
    opacity: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const photosInput = document.getElementById('photos-input');
    const previewContainer = document.getElementById('preview-container');
    const submitContainer = document.getElementById('submit-button-container');
    
    let fileStore = new DataTransfer();

    // Функція для відображення мініатюр
    const renderPreviews = () => {
        // Перед перемальовкою очищуємо старі blob URL, щоб уникнути витоків пам'яті
        previewContainer.querySelectorAll('img[src^="blob:"]').forEach(img => {
            URL.revokeObjectURL(img.src);
        });

        previewContainer.innerHTML = '';
        submitContainer.style.display = fileStore.files.length > 0 ? 'flex' : 'none';

        Array.from(fileStore.files).forEach((file, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            previewItem.dataset.filename = file.name;

            // Створюємо миттєве посилання на файл
            const objectUrl = URL.createObjectURL(file);

            previewItem.innerHTML = `
                <button type="button" class="preview-delete-btn" title="Видалити">&times;</button>
                <img src="${objectUrl}" alt="${file.name}">
                <input type="text" name="notes[${index}]" class="form-control note-input" placeholder="Примітка...">
            `;
            previewContainer.appendChild(previewItem);
        });
        
        photosInput.files = fileStore.files;
    };

    photosInput.addEventListener('change', () => {
        fileStore = new DataTransfer();
        for (const file of photosInput.files) {
            fileStore.items.add(file);
        }
        renderPreviews();
    });

    // Обробка перетягування файлів
    previewContainer.addEventListener('click', (event) => {
        if (!event.target.classList.contains('preview-delete-btn')) return;
        
        const itemToRemove = event.target.closest('.preview-item');
        const filenameToRemove = itemToRemove.dataset.filename;

        // 1. Додаємо клас для запуску CSS-анімації зникання
        itemToRemove.classList.add('is-deleting');
        
        // 2. Чекаємо завершення анімації (300 мс, як в CSS)
        setTimeout(() => {
            // 3. Виконуємо логіку видалення файлу зі списку
            const newFileStore = new DataTransfer();
            for (const file of fileStore.files) {
                if (file.name !== filenameToRemove) {
                    newFileStore.items.add(file);
                }
            }
            fileStore = newFileStore;
            
            // 4. Перемальовуємо мініатюри. Видалений елемент зникне,
            // а решта плавно займуть його місце завдяки CSS transition.
            renderPreviews();
        }, 300);
    });
});
</script>