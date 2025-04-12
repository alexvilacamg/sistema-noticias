import os

# Caminho da pasta raiz do projeto
root_folder = r"C:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias"

# Arquivo de saída (garanta que esteja dentro do root_folder)
output_file = os.path.join(root_folder, "compiled_code.txt")

# Extensões de arquivos que consideraremos como código
code_extensions = ('.php', '.html', '.css', '.js', '.json', '.txt')

# Diretórios que serão ignorados (por exemplo, cache, logs e .git)
skip_dirs = {"cache", "logs", ".git"}

def gerar_arvore(diretorio, arquivo, prefixo=""):
    """
    Percorre recursivamente o diretório e escreve a estrutura no arquivo.
    
    Args:
        diretorio (str): Caminho do diretório a ser listado.
        arquivo (file object): Objeto de arquivo aberto para escrita.
        prefixo (str): Espaçamento para formatação da árvore.
    """
    try:
        itens = sorted(os.listdir(diretorio))
    except OSError as e:
        arquivo.write(prefixo + f"Erro ao acessar {diretorio}: {e}\n")
        return

    # Filtra itens se for diretório ignorado (apenas para diretórios, arquivos serão listados normalmente)
    itens_filtrados = []
    for item in itens:
        caminho_completo = os.path.join(diretorio, item)
        # Se for diretório e estiver na lista de ignorados, pule-o
        if os.path.isdir(caminho_completo) and os.path.basename(caminho_completo) in skip_dirs:
            continue
        itens_filtrados.append(item)

    for indice, item in enumerate(itens_filtrados):
        caminho_completo = os.path.join(diretorio, item)
        if indice == len(itens_filtrados) - 1:
            ponteiro = "└── "
            extensao = "    "
        else:
            ponteiro = "├── "
            extensao = "│   "

        arquivo.write(prefixo + ponteiro + item + "\n")
        if os.path.isdir(caminho_completo):
            gerar_arvore(caminho_completo, arquivo, prefixo + extensao)

# Abre o arquivo de saída para escrita
with open(output_file, "w", encoding="utf-8") as outfile:
    # Escreve a estrutura do diretório
    outfile.write("ESTRUTURA DO DIRETÓRIO:\n")
    outfile.write("=" * 80 + "\n\n")
    gerar_arvore(root_folder, outfile)
    
    # Adiciona um separador
    outfile.write("\n" + "=" * 80 + "\n")
    outfile.write("CONTEÚDO DOS ARQUIVOS:\n")
    outfile.write("=" * 80 + "\n\n")
    
    # Percorre recursivamente a pasta raiz para compilar os arquivos
    for dirpath, dirnames, filenames in os.walk(root_folder):
        # Remove os diretórios ignorados da busca
        dirnames[:] = [d for d in dirnames if d not in skip_dirs]
        
        for filename in filenames:
            file_path = os.path.join(dirpath, filename)
            
            # Pula o próprio arquivo de saída para não incluir seu conteúdo
            if os.path.abspath(file_path) == os.path.abspath(output_file):
                continue
            
            # Verifica se o arquivo possui uma das extensões desejadas
            if not filename.lower().endswith(code_extensions):
                continue
            
            outfile.write("=" * 80 + "\n")
            outfile.write(f"Arquivo: {file_path}\n")
            outfile.write("=" * 80 + "\n\n")
            
            try:
                with open(file_path, "r", encoding="utf-8") as infile:
                    content = infile.read()
                    outfile.write(content)
                    outfile.write("\n\n")
            except Exception as e:
                outfile.write(f"Erro ao ler o arquivo: {e}\n\n")
                    
print(f"Compilação concluída. Veja o arquivo '{output_file}'.")
