import os

# Caminho da pasta raiz do projeto
root_folder = r"C:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias"

# Arquivo de saída que receberá todo o código compilado
output_file = "compiled_code.txt"

# Extensões de arquivos que consideraremos como código
code_extensions = ('.php', '.html', '.css', '.js', '.json', '.txt')

with open(output_file, "w", encoding="utf-8") as outfile:
    for dirpath, dirnames, filenames in os.walk(root_folder):
        for filename in filenames:
            # Se o arquivo tiver uma das extensões de código definidas, vamos processá-lo
            if filename.lower().endswith(code_extensions):
                file_path = os.path.join(dirpath, filename)
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
