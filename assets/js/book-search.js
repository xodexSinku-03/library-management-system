document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('searchInput');
    const dataList = document.getElementById('bookList');
    const button = document.getElementById('searchButton');
    const resultDiv = document.getElementById('searchResult');

    if (!input || !dataList || !button || !resultDiv) {
        console.error('Missing DOM elements');
        return;
    }

    const performSearch = () => {
        const query = input.value.trim().toLowerCase();
        if (!query) {
            resultDiv.innerHTML = '<p>Please enter a search term.</p>';
            return;
        }
        fetch('data/books.json')
            .then(res => {
                if (!res.ok) throw new Error('Fetch failed: ' + res.status);
                return res.json();
            })
            .then(books => {
                const filtered = books.filter(b =>
                    b.title.toLowerCase().includes(query) ||
                    b.author.toLowerCase().includes(query)
                );
                if (filtered.length === 0) {
                    resultDiv.innerHTML = '<p>No books found.</p>';
                    return;
                }
                resultDiv.innerHTML = filtered.map((b, index) => `
                    <div class="book-card">
                        <div class="title-price">
                            <h4>${b.title}</h4>
                            <span class="book-price">$${b.price ? Number(b.price).toFixed(2) : 'N/A'}</span>
                        </div>
                        <p><strong>Author:</strong> ${b.author}</p>
                        <p>${b.description}</p>
                        <a href="${b.link}" target="_blank">More info</a>
                        <form action="pages/buy-book.php" method="POST" style="display:inline;">
                            <input type="hidden" name="book_index" value="${index}">
                            <button type="submit" class="buy-btn" ${!b.price || b.price <= 0 ? 'disabled' : ''}>Buy</button>
                        </form>
                    </div>
                `).join('');
            })
            .catch(err => {
                console.error('Search error:', err);
                resultDiv.innerHTML = '<p>Something went wrong. Try again later.</p>';
            });
    };

    input.addEventListener('input', () => {
        const q = input.value.trim();
        if (!q) {
            dataList.innerHTML = '';
            return;
        }
        fetch(`suggest.php?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(titles => {
                dataList.innerHTML = titles
                    .map(title => `<option value="${title}">`)
                    .join('');
            })
            .catch(err => console.error('Suggestion error:', err));
    });

    button.addEventListener('click', performSearch);

    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
});