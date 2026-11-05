"""Exercise the real PHP/session/SQLite flow against a disposable database."""
from datetime import date, timedelta
from contextlib import closing
from http.cookiejar import CookieJar
from pathlib import Path
import os
import re
import socket
import sqlite3
import subprocess
import tempfile
import time
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode
from urllib.request import build_opener, HTTPCookieProcessor

ROOT = Path(__file__).resolve().parents[1]

with tempfile.TemporaryDirectory(prefix='eateasy-check-') as folder:
    database = Path(folder) / 'bookings.sqlite'
    subprocess.run(['php', 'scripts/demo.php', str(database)], cwd=ROOT, check=True)
    with socket.socket() as address:
        address.bind(('127.0.0.1', 0))
        port = address.getsockname()[1]
    env = dict(os.environ, EATEASY_DSN='sqlite:' + str(database), EATEASY_DEMO='1')
    with open(Path(folder) / 'php.log', 'w', encoding='utf-8') as log:
        server = subprocess.Popen(['php', '-S', f'127.0.0.1:{port}', 'router.php'], cwd=ROOT, env=env, stdout=log, stderr=log)
        client = build_opener(HTTPCookieProcessor(CookieJar()))
        def request(path, data=None):
            try:
                with client.open(f'http://127.0.0.1:{port}/{path}', None if data is None else urlencode(data).encode(), timeout=5) as response:
                    return response.status, response.read().decode(), response.url
            except HTTPError as error:
                return error.code, error.read().decode(), error.url
        def token(page):
            return re.search(r'name="csrf" value="([a-f0-9]+)"', page)[1]
        try:
            for attempt in range(40):
                try:
                    status, login, _ = request('index.php')
                    break
                except URLError:
                    time.sleep(.1)
            else:
                raise AssertionError('PHP host did not start')
            assert status == 200
            assert request('index.php', {'email': 'alex@example.test', 'password': 'EatEasy-demo-2026'})[0] == 403
            status, dashboard, _ = request('index.php', {'csrf': token(login), 'email': 'alex@example.test', 'password': 'EatEasy-demo-2026'})
            assert status == 200 and '<h1>Restaurantes</h1>' in dashboard
            assert 'loremflickr' not in dashboard
            assert request('cancelar_reserva.php?id=1')[0] == 405
            assert request('var/demo.sqlite')[0] == 404
            _, booking, _ = request('reservar.php?id=1')
            day = (date.today() + timedelta(days=3)).isoformat()
            fields = {'csrf': token(booking), 'restaurante_id': '1', 'dia': day, 'hora': '19:30', 'pessoas': '3'}
            status, reservations, location = request('reservar.php?id=1', fields)
            assert status == 200 and 'msg=criado' in location
            status, conflict, _ = request('reservar.php?id=1', fields)
            assert status == 200 and 'Já tem reserva para esta refeição.' in conflict
            assert f'value="{day}"' in conflict and 'value="19:30" selected' in conflict and 'value="3" selected' in conflict
            with closing(sqlite3.connect(database)) as db:
                count = db.execute('SELECT COUNT(*) FROM reservas').fetchone()[0]
                booking_id = db.execute('SELECT MAX(id) FROM reservas').fetchone()[0]
            request('reservar.php?id=999999', dict(fields, restaurante_id='999999'))
            with closing(sqlite3.connect(database)) as db:
                assert db.execute('SELECT COUNT(*) FROM reservas').fetchone()[0] == count
            assert request('cancelar_reserva.php', {'id': booking_id})[0] == 403
            status, cancelled, location = request('cancelar_reserva.php', {'csrf': fields['csrf'], 'id': booking_id})
            assert status == 200 and 'msg=cancelado' in location
            assert 'Reserva cancelada com sucesso.' in cancelled
            with closing(sqlite3.connect(database)) as db:
                assert db.execute('SELECT COUNT(*) FROM reservas').fetchone()[0] == count - 1
            request('logout.php')
            _, login, _ = request('index.php')
            _, admin, _ = request('index.php', {'csrf': token(login), 'email': 'admin@example.test', 'password': 'EatEasy-demo-2026'})
            assert 'Painel de Controlo' in admin
            _, creation, _ = request('admin_restaurante.php')
            admin_token = token(creation)
            status, _, location = request('admin_restaurante.php', {'csrf': admin_token, 'nome': 'Verification kitchen', 'local': 'Lisboa', 'preco': '25', 'cozinha': 'Portuguesa'})
            assert status == 200 and 'msg=criado' in location
            with closing(sqlite3.connect(database)) as db:
                restaurant_id = db.execute('SELECT MAX(id) FROM restaurantes').fetchone()[0]
            assert request(f'remover_restaurante.php?id={restaurant_id}')[0] == 405
            assert request('remover_restaurante.php', {'id': restaurant_id})[0] == 403
            status, _, location = request('remover_restaurante.php', {'csrf': admin_token, 'id': restaurant_id})
            assert status == 200 and 'msg=removido' in location
            with closing(sqlite3.connect(database)) as db:
                assert db.execute('SELECT ativo FROM restaurantes WHERE id=?', (restaurant_id,)).fetchone()[0] == 0
            print('PASS: real sign-in, CSRF, bookings, conflicts, retained fields, cancellation, admin creation/removal and private storage.')
        finally:
            server.terminate()
            server.wait(timeout=10)
