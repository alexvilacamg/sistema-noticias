# Sistema de Notícias

Um sistema em PHP para coletar e exibir as principais notícias de política dos portais brasileiros (G1, UOL, Folha) por meio de técnicas de scraping. O sistema agrega informações de múltiplas fontes, normaliza os dados (como datas de publicação) e os apresenta em uma interface interativa com múltiplas visualizações.

## Sumário

- [Recursos](#recursos)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Arquitetura e Estrutura](#arquitetura-e-estrutura)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Sistema de Cache](#sistema-de-cache)
- [Testes](#testes)
- [Automação](#automação)
- [Contribuição](#contribuição)
- [Licença](#licença)

## Recursos

- **Scraping Modular:**  
  Cada portal possui sua própria classe que estende AbstractNewsScraper, facilitando a manutenção e a inclusão de novos portais.

- **Sistema de Cache Flexível:**  
  Suporte para múltiplos backends (Redis, Memcached, FileCache) com fallback automático em caso de falhas.

- **Interface Interativa:**  
  Visualização dual (cards/tabela) com DataTables para ordenação, busca e paginação.

- **Filtros de Fonte:**  
  Filtragem por portais de notícia (G1, UOL, Folha) com persistência de preferências.

- **Modo Escuro:**  
  Suporte para tema claro/escuro adaptável às preferências do usuário.

- **Logs em Tempo Real:**  
  Sistema de logs com múltiplos níveis e visualização na interface admin.

- **Painel Administrativo:**  
  Interface para gerenciamento de cache e monitoramento do sistema.

- **Atualizações Assíncronas:**  
  Processamento em background com atualizações em tempo real via Server-Sent Events.

## Tecnologias Utilizadas

- **Backend:** PHP 7.4+
- **Frontend:** HTML5, CSS3, JavaScript (ES6)
- **Bibliotecas:**
  - DataTables para visualização tabular
  - Predis para conexão com Redis
  - PHPUnit e Mockery para testes
  - phpdotenv para gestão de variáveis de ambiente
- **Cache:** Redis/Memcached com fallback para sistema de arquivos
- **Ferramentas:** cURL, DOMDocument, DOMXPath
- **Autoloading:** PSR-4 via Composer

## Arquitetura e Estrutura

O sistema segue uma arquitetura MVC com namespaces PSR-4:

```
sistema-noticias/
├── src/
│   └── App/
│       ├── Cache/            # Implementações de cache
│       ├── Controllers/      # Controllers da aplicação
│       ├── Models/           # Scrapers e modelos
│       ├── Utils/            # Classes utilitárias
│       └── Views/            # Templates de visualização
├── tests/                    # Testes unitários
│   ├── Cache/                # Testes do sistema de cache
│   └── Scrapers/             # Testes dos scrapers
├── config/                   # Arquivos de configuração
├── public/                   # Arquivos públicos e API
├── cache/                    # Dados em cache (ignorado pelo Git)
├── logs/                     # Arquivos de log (ignorado pelo Git)
└── vendor/                   # Dependências (gerenciado pelo Composer)
```

## Instalação

1. Clone o repositório:
   ```
   git clone https://github.com/seu-usuario/sistema-noticias.git
   cd sistema-noticias
   ```

2. Instale as dependências via Composer:
   ```
   composer install
   ```

3. Copie o arquivo de ambiente de exemplo:
   ```
   cp .env.example .env
   ```

4. Configure seu arquivo `.env` com suas credenciais:
   ```
   CACHE_TYPE=file
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

5. Certifique-se que os diretórios `cache` e `logs` existem e são graváveis:
   ```
   mkdir -p cache logs
   chmod 777 cache logs
   ```

6. Configure seu servidor web para apontar para o diretório `public/` ou use o servidor embutido do PHP:
   ```
   php -S localhost:8000 -t public/
   ```

## Configuração

### Variáveis de Ambiente

Configure suas variáveis no arquivo `.env`:

```
# Configuração de Cache
CACHE_TYPE=file  # file, redis ou memcached
CACHE_TTL=600

# Redis (se CACHE_TYPE=redis)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DATABASE=0

# Memcached (se CACHE_TYPE=memcached)
MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211
```

### Adicionando Novos Scrapers

Para adicionar um novo portal de notícias:

1. Crie uma classe em `src/App/Models/` que estenda `AbstractNewsScraper`
2. Implemente os métodos necessários
3. Adicione ao arquivo `config/scrapers_config.php`

## Sistema de Cache

O sistema implementa uma estratégia de cache flexível:

- **Redis**: Para alta performance e ambientes distribuídos
- **Memcached**: Alternativa para cache em memória
- **FileCache**: Sistema de fallback baseado em arquivos

A interface administrativa permite:
- Visualizar estatísticas de cache
- Limpar o cache manualmente
- Pré-aquecer o cache com dados atualizados

## Testes

O projeto usa PHPUnit para testes unitários:

```bash
# Executar todos os testes
composer test

# Executar apenas testes de cache
composer test:cache

# Executar apenas testes de scrapers
composer test:scrapers
```

## Automação

Configure um cron job para atualizar periodicamente as notícias:

```
*/30 * * * * php /caminho/para/sistema-noticias/background_scrape.php
```

A interface também oferece um botão para forçar a atualização manual.

## Contribuição

Contribuições são bem-vindas! Se desejar sugerir melhorias ou corrigir eventuais bugs, sinta-se à vontade para abrir _issues_ ou enviar _pull requests_.

## Licença

Este projeto é licenciado sob a [MIT License](LICENSE).
