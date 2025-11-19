from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
import json
import subprocess
import os

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://10.150.28.171","http://10.150.28.171:8080"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Aseguramos rutas absolutas para evitar errores de directorio
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CHAT_FILE = os.path.join(BASE_DIR, "chat_history.json")
DATA_FILE = os.path.join(BASE_DIR, "ventas_data.json")

# Crear los archivos si no existen
for file in [CHAT_FILE, DATA_FILE]:
    if not os.path.exists(file):
        with open(file, "w", encoding="utf-8") as f:
            if "ventas" in file:
                f.write(json.dumps({"ventas": []}))
            else:
                f.write("")

@app.post("/chat")
async def chat(request: Request):
    try:
        data = await request.json()
        user_input = data.get("message", "").strip()
        if not user_input:
            return {"response": "Por favor, escribe un mensaje."}

        # Lee los datos de ejemplo (ventas)
        with open(DATA_FILE, "r", encoding="utf-8") as f:
            empresa_data = json.load(f)

        # Construir prompt
        prompt = f"""
        Eres un asistente de análisis de datos empresariales. 
        Usa los siguientes datos JSON si el usuario pide información concreta:
        {json.dumps(empresa_data, indent=2)}

        Pregunta del usuario: {user_input}
        """

        # Ejecutar Ollama y obtener la respuesta
        result = subprocess.run(
            ["ollama", "run", "llama3", prompt],
            capture_output=True,
            text=True
        )

        if result.returncode != 0:
            respuesta = f"Error de Ollama: {result.stderr.strip()}"
        else:
            respuesta = result.stdout.strip()

        # Guardar historial
        with open(CHAT_FILE, "a", encoding="utf-8") as f:
            f.write(json.dumps({"user": user_input, "bot": respuesta}, ensure_ascii=False) + "\n")

        return {"response": respuesta}

    except Exception as e:
        return {"response": f"Error en el servidor: {e}"}
@app.post("/reset_chat")
async def reset_chat():
    """Borra el historial del chat (archivo JSON)"""
    try:
        with open(CHAT_FILE, "w", encoding="utf-8") as f:
            f.write("")  # Limpia el archivo
        return {"message": "Historial de chat borrado correctamente"}
    except Exception as e:
        return {"error": str(e)}
