import subprocess

def generar_respuesta(prompt):
    try:
        res = subprocess.run(
            ["ollama", "run", "llama3", prompt],
            capture_output=True,
            text=True
        )
        return res.stdout.strip()
    except Exception:
        return "Hubo un error al conectar con el modelo "
