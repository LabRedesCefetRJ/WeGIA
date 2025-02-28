# WeGIA
WeGIA: Web Gerenciador para Instituições Assistenciais 

Este projeto desenvolve/mantém um sistema integrado de gestão licenciado sob a GNU GPL v3.0, que concede as liberdades de executar, modificar e redistribuir cópias do software. Ele pode ser utilizado para melhorar a gestão, controle e transparência de entidades que prestam serviços de utilidade pública para a sociedade. 

A versão atual do sistema conta com os módulos: 
1) Pessoas, para cadastro de funcionários e atendidos; 
2) Material e Patrimônio, para controle de almoxarifado e doações; 
3) Memorando, para troca de mensagens institucionais entre os diversos setores, diminuindo o fluxo de papel; 
4) Contribuição, para captação de recursos através de doações via cartão de crédito ou boleto bancário;
5) Saúde, para gerenciamento do prontuário médico e controle de medicação dos atendidos e também funcionários;
6) Pet, para cadastro de animais atendidos.

Contato: [Prof. Nilson  Lazarin](https://bsi.cefet-rj.br/~lazarin/)
<hr>

## Teste online: http://demo.wegia.org

## Como instalar?
Em um terminal execute os comandos abaixo:
```
apt update
apt install wget dialog -y
wget https://raw.githubusercontent.com/LabRedesCefetRJ/WeGIA/refs/heads/master/instalador/install.sh
chmod +x install.sh
./install.sh
```

## Copyright
<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /></a><br />WeGIA is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International License</a>. The licensor cannot revoke these freedoms as long as you follow the license terms:

* __Attribution__ — You must give __appropriate credit__ like below:

LAZARIN, Nilson Mori; ESCALFONI, Rafael Elias de Lima; FERREIRA, Vinícius Marques da Silva. WeGIA: Web Gerenciador para Instituições Assistenciais. In: CONGRESSO LATINO-AMERICANO DE SOFTWARE LIVRE E TECNOLOGIAS ABERTAS (LATINOWARE), 21. , 2024, Foz do Iguaçu/PR. Anais [...]. Porto Alegre: Sociedade Brasileira de Computação, 2024 . p. 358-366. DOI: https://doi.org/10.5753/latinoware.2024.245668. 

<details>
<summary> Cite using Bibtex </summary>

```
@inproceedings{latinoware,
 author = {Nilson Lazarin and Rafael Elias Escalfoni and Vinícius Ferreira},
 title = { WeGIA: Web Gerenciador para Instituições Assistenciais},
 booktitle = {Anais do XXI Congresso Latino-Americano de Software Livre e Tecnologias Abertas},
 location = {Foz do Iguaçu/PR},
 year = {2024},
 keywords = {},
 issn = {0000-0000},
 pages = {358--366},
 publisher = {SBC},
 address = {Porto Alegre, RS, Brasil},
 doi = {10.5753/latinoware.2024.245668},
 url = {https://sol.sbc.org.br/index.php/latinoware/article/view/31544}
}
```
</details>
