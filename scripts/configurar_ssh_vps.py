#!/usr/bin/env python3

import paramiko
import sys

# Configurações
VPS_IP = "76.13.167.54"
VPS_USER = "root"
VPS_PASSWORD = "1Qaz2wsx@2026"

# Ler chave pública
with open(f"{sys.path[0]}/../../.ssh/cobranca_deploy.pub", "r") as f:
    SSH_PUB_KEY = f.read().strip()

print(f"🔑 Configurando chave SSH na VPS {VPS_IP}...")

# Conectar na VPS
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(VPS_IP, username=VPS_USER, password=VPS_PASSWORD)

# Executar comandos
commands = [
    f"mkdir -p /root/.ssh",
    f"echo '{SSH_PUB_KEY}' >> /root/.ssh/authorized_keys",
    f"chmod 700 /root/.ssh",
    f"chmod 600 /root/.ssh/authorized_keys",
    f"systemctl restart ssh",
    f"echo '✓ Chave SSH configurada com sucesso!'"
]

for cmd in commands:
    stdin, stdout, stderr = ssh.exec_command(cmd)
    output = stdout.read().decode()
    error = stderr.read().decode()
    if output:
        print(output)
    if error:
        print(f"Erro: {error}")

ssh.close()
print("✓ Configuração concluída!")
