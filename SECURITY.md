# Política de Segurança

## Como contribuir com a segurança do WeGIA?
Você pode contribuir com o projeto WeGIA, analisando o código em tempo de design, executando uma instância localmente em seu computador ou utilizando um servidor de testes

### Em tempo de design:
Para testar o código do WeGIA em tempo de design, você pode utilizar ferramentas de análise de código, clone o repositório e teste localmente. Abaixo segue algumas dicas de ferramentas para isso:

- mn-analise: 
    - Leia o whitepaper [Uma extensão para o VSCode que utiliza o ChatGPT como ferramenta de apoio ao desenvolvimento de software seguro](https://periodicos.univali.br/index.php/acotb/article/view/20376).
    - Disponível para download no [Visual-Studio Marketplace](https://marketplace.visualstudio.com/items/MustafaNeto.mn-analise/)

### Em tempo de execução (instância local)
Você pode utilizar uma máquina virtual com o WeGIA pré instalado para executar seus testes de segurança.

- VirtualBox
    - Pré requisito: Instale o [VirtualBox](https://www.virtualbox.org/wiki/Downloads) no seu computador.
    - Realize o download da [Máquina virtual do WeGIA](https://www.wegia.org/vm/)
        - usuário da máquina: wegia
        - senha do usuário: wegia
    - [Assista o tutorial do uso da VM do WeGIA no Youtube](https://youtu.be/mGayZb2snqk)

- Instalação Local

Execute em um terminal os seguintes comandos:  

```
apt update
apt install wget dialog -y
wget https://raw.githubusercontent.com/LabRedesCefetRJ/WeGIA/refs/heads/master/instalador/install.sh
chmod +x install.sh
./install.sh
```

### Em tempo de execução (servidor de teste)
Você pode utilizar um servidor público com o WeGIA pré instalado para executar seus testes de segurança.

- [Servidor para testes de segurança](https://comfirewall.wegia.org:8000/) 


