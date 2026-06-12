FROM python:3.11-alpine
RUN pip install --no-cache-dir slowloris
COPY api.py /api.py
EXPOSE 8888
CMD ["python", "/api.py"]
