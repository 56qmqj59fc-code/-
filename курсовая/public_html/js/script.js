function toggleDescription(id, btn) {
    const textElement = document.getElementById('desc-' + id);

    if (textElement.classList.contains('expanded')) {
        textElement.classList.remove('expanded');
        btn.innerText = 'показать больше';
    } else {
        textElement.classList.add('expanded');
        btn.innerText = 'свернуть';
    }
}