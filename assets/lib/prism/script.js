document.addEventListener('DOMContentLoaded', function () {
    jQuery('.tutor-container pre').each(function () {
        let el = jQuery(this),
            fallback = 'javascript',
            lang = el.attr('class').trim().replace('language-', '') || fallback,
            highlighted = null;

        if (Prism) {
            try {
                highlighted = Prism.highlight(el.text(), Prism.languages[lang], lang);
            } catch (error) {
                highlighted = Prism.highlight(el.text(), Prism.languages[fallback], fallback);
            }

            highlighted ? el.html(highlighted) : null
        }
    })
})