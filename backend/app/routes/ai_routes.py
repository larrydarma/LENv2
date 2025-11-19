from fastapi import APIRouter
from app.ai_model import resumir_texto

router = APIRouter()

@router.post("/resumir")
def resumir(data: dict):
    texto = data.get("texto", "")
    return {"resultado": resumir_texto(texto)}
