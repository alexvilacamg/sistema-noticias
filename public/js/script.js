// Função que busca as notícias via endpoint JSON
function fetchNews() {
    fetch('/public/api/news.php')
        .then(response => response.json())
        .then(data => updateNews(data))
        .catch(error => console.error('Erro ao buscar notícias:', error));
}

// Atualiza o conteúdo do container com as notícias recebidas
function updateNews(data) {
    const container = document.getElementById('newsContainer');
    if (data && data.length > 0) {
        let html = '<ul>';
        data.forEach(item => {
            html += `<li>
                        <a href="${item.url}" target="_blank">${item.title}</a>
                        <p>${item.description}</p>
                     </li>`;
        });
        html += '</ul>';
        container.innerHTML = html;
    } else {
        container.innerHTML = '<p>Nenhuma notícia encontrada.</p>';
    }
}

// Chamada inicial para carregar as notícias imediatamente
fetchNews();

// Atualiza as notícias a cada 60 segundos (60000 milissegundos)
setInterval(fetchNews, 60000);
