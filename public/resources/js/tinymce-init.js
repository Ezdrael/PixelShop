// public/resources/js/tinymce-init.js

document.addEventListener('DOMContentLoaded', () => {
    // Шукаємо дані про користувачів, які ми передали з контролера
    const usersDataElement = document.getElementById('users-data');
    if (!usersDataElement) return;

    const users = JSON.parse(usersDataElement.textContent);
    const userMentions = users.map(user => ({
        id: user.id,
        text: user.name
    }));

    tinymce.init({
        selector: '#event-description', // Прив'язуємо до нашого <textarea>
        height: 300,
        language: 'uk',
        language_url: `${projectUrl}/resources/tinymce/langs/uk.js`,
        plugins: 'autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount mentions',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | spoiler | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | help',
        
        // Налаштування для згадувань
        mentions_fetch: (query, success) => {
            const filteredUsers = userMentions.filter(user => {
                return user.text.toLowerCase().includes(query.term.toLowerCase());
            });
            success(filteredUsers);
        },
        mentions_item_tpl: '<li class="mention-item" data-id="{0}">{1}</li>',

        // ✅ Створення кастомної кнопки "Спойлер"
        setup: function(editor) {
            editor.ui.registry.addButton('spoiler', {
                text: 'Спойлер',
                icon: 'comment-add',
                tooltip: 'Приховати текст (спойлер)',
                onAction: function() {
                    const selectedText = editor.selection.getContent({ format: 'text' });
                    if (selectedText) {
                        editor.selection.setContent(`<span class="spoiler">${selectedText}</span>`);
                    }
                }
            });
        }
    });
});