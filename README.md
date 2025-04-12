# Sistema de Notícias

Um sistema em PHP para coletar e exibir as principais notícias de política dos portais brasileiros (G1, UOL, etc.) por meio de técnicas de scraping. O sistema agrega informações de múltiplas fontes, normaliza os dados (como datas de publicação) e os apresenta em uma tabela interativa com ordenação, busca e paginação, utilizando DataTables.

## Sumário

- [Recursos](#recursos)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Instalação](#instalacao)
- [Automação e Cache](#automação-e-cache)
- [Contribuição](#contribuição)
- [Licença](#licença)

## Recursos

- **Scraping Modular:**  
  Cada portal possui sua própria classe (ex.: G1Scraper, UOLScraper) que implementa uma interface comum, facilitando a manutenção e a inclusão de novos portais.

- **Agregação:**  
  As notícias coletadas de todas as fontes são combinadas por um agregador (Scraper.php) que também normaliza os dados, como datas de publicação.

- **Interface Interativa:**  
  A apresentação dos dados é feita por meio de uma tabela interativa (DataTables), que oferece ordenação, busca e paginação.

- **Logs de Depuração:**  
  Uma área na interface exibe os logs de depuração para acompanhar a execução em tempo real.

## Tecnologias Utilizadas

- **Linguagem:** PHP
- **Front-end:** HTML5, CSS, JavaScript
- **Biblioteca de Tabela Interativa:** [DataTables](https://datatables.net/)
- **Ferramentas de Scraping:** cURL, DOMDocument, DOMXPath
- **Controle de Versão:** Git
- **Hospedagem do Código:** GitHub

## Estrutura do Projeto

sistema-noticias/
├── app/
│   ├── controllers/
│   │   └── NewsController.php
│   └── models/
│       ├── G1Scraper.php
│       ├── UOLScraper.php
│       ├── NewsScraperInterface.php
│       └── Scraper.php
├── cache/
│   └── (arquivos gerados pelo cache)
├── config/
│   └── config.php
├── logs/
│   └── debug.log
├── public/
│   ├── api/
│   │   └── news.php
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── index.php
└── background_scrape.php

## Instalação

1. Clone o repositório
2. Execute `composer install`
3. Copie `.env.example` para `.env`
4. Configure suas credenciais no arquivo `.env`
5. Certifique-se de que os diretórios `cache` e `logs` são graváveis
6. Acesse a aplicação via navegador

## Automação e Cache

O sistema utiliza arquivos de cache para evitar execuções intensivas de scraping a cada acesso. O script `background_scrape.php` pode ser executado periodicamente (por meio do agendador de tarefas do sistema ou disparado automaticamente pelo próprio sistema) para atualizar os dados.

## Contribuição

Contribuições são bem-vindas! Se desejar sugerir melhorias ou corrigir eventuais bugs, sinta-se à vontade para abrir _issues_ ou enviar _pull requests_.

> **Observação:** Este sistema é de uso pessoal, mas você pode adaptá-lo conforme suas necessidades.

## Licença

Este projeto é licenciado sob a [MIT License](LICENSE).
