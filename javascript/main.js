(() => {

    const contents = document.getElementById('content');

    // 
    const infiniteScrollObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if ( ! entry.isIntersecting ) return;

            infiniteScrollObserver.unobserve(entry.target);
            loadContent();

        });
    });

    // 
    let i = 0;
    const max = 30;

    function loadContent() {

        const response = await fetchDummy('https://example.com/load?i=' + i);

        contents.insertAdjacentHTML('beforeend',
            '<div>' +
            '#' + (i + 1) + '<br>' +
            await response.text() +
            '</div>');

        // 
        i++;

        if (i < max)
            infiniteScrollObserver.observe(contents.lastElementChild);

    }

    // 
    loadContent();

})();