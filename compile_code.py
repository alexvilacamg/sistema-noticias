import os

# Caminho da pasta raiz do projeto
root_folder = r"C:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias"

# Arquivo de saída (garanta que esteja dentro do root_folder)
output_file = os.path.join(root_folder, "compiled_code.txt")

# Extensões de arquivos que consideraremos como código
code_extensions = ('.php', '.html', '.css', '.js', '.json', '.txt')

# Diretórios que serão ignorados (por exemplo, cache e logs)
skip_dirs = {"cache", "logs"}

with open(output_file, "w", encoding="utf-8") as outfile:
    # Percorre recursivamente a pasta raiz
    for dirpath, dirnames, filenames in os.walk(root_folder):
        # Remove dos diretórios a serem buscados os que estão na lista de ignorados
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
