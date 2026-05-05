# Alexa Skill - Via Scriptura

Skill Alexa criada para usar o JSON remoto do projeto como fonte de conteúdo:
`https://raw.githubusercontent.com/alexandrebsimao/lpmc/refs/heads/main/data/data.json`

## Comportamento

- Ao abrir a skill (`alexa, abrir via scriptura`), ela pergunta:
  - "Você quer a leitura do dia ou ouvir uma história da Bíblia?"
- Se o usuário pedir leitura do dia, responde com:
  - "A leitura do dia é: ..." + conteúdo do dia
- Se o usuário pedir uma história, busca no JSON remoto por título/referência e conta a história.

## Estrutura

- `lambda/index.js`: lógica da skill
- `lambda/package.json`: dependências da Lambda
- `interaction-model/pt-BR.json`: modelo de interação da Alexa

## Deploy (resumo)

1. Entre em `alexa-via-scriptura-skill/lambda`
2. Instale dependências com `npm install`
3. Faça deploy da Lambda com `index.js`
4. No Alexa Developer Console, importe `interaction-model/pt-BR.json`

## Observação

A skill consome o JSON diretamente da URL raw do GitHub em tempo de execução.
