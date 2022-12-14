# LOG PROCESSOR
<hr>

**Log Processor** é uma aplicação realizada como teste backend para [Melhor Envio](https://melhorenvio.com.br/), é pensando para realizar o processamento de dados oriundos de um arquivo .txt, realizar o salvamento de dados importantes no banco de dados e posteriormente realizar a geração de relatórios com base nos dados coletados.

## Tecnologias utilizadas

Optado pela utilização do [Laravel](https://laravel.com/) com [Eloquent](https://laravel.com/docs/9.x/eloquent), [MySQL](https://www.mysql.com/) e [PHPUnit](https://phpunit.de/).

## Como executar/reproduzir a aplicação
1. Inicialmente realize o clone do projeto utilizando um dos comandos abaixo, conforme preferência:
```bash
# ssh
git clone git@github.com:williamtrevisan/log-processor.git

# https
git clone https://github.com/williamtrevisan/log-processor.git
```

2. Acesse a pasta em que o arquivo foi clonado:
```bash
cd log-processor
```

3. Crie o arquivo .env realizando a copia do mesmo com base no .env.example, abaixo comando para realizar a copia:
```bash
cp .env.example .env
```

4. Tendo efetuado a copia/criação do arquivo .env atualize as informações do BD com base nas informadas abaixo:
```bash
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=log_processor
DB_USERNAME=root
DB_PASSWORD=root
```

5. Suba o container:
```bash
docker-compose up -d
```

6. Acesse o container:
```bash
docker-compose exec app bash
```

7. Realize a instalação das dependências:
```bash
composer install
```
 
8. Realize a geração da chave da aplicação:
```bash
php artisan key:generate
```

9. Execute a criação da tabela:
```bash
php artisan migrate
```

## Para execução dos testes de unidade, integração e e2e execute o comando abaixo:
```bash
php artisan test
```

## Para execução dos testes manualmente:

### **POST** - Processamento de Requests
> http://localhost:8000/api/process_requests

Para possibilitar o processamento das solicitações disponibilizadas no arquivo logs.txt, foi criado o endpoint: api/process_requests, o mesmo é responsável por estar realizando a validação da validade do arquivo - se o mesmo é o arquivo contendo registros de solicitações conforme o exemplo disponibilizado no email, realizar a limpeza do banco de dados a fim de que após cada requisição não sejam acrescidos mais dados do que existem no arquivo mantendo sempre os 100k, e também realizar o processamento dos dados adicionando-os em um EventLoop de 1000 em 1000 para realização do salvamento.

**Exemplo de Solicitação** 
> *Também é possível estar utilizando o Postman ou Insomnia enviando o arquivo com a chave 'requests'*
```bash
# OBS: Executar fora do container 

curl --location --request POST  'http://localhost:8000/api/process_requests' \
--header 'Accept: application/json' \
--header 'Content-Type: multipart/form-data' \
--form 'requests=@"path/to/file.txt"'
```

### **GET** - Geração dos Relatórios
> http://localhost:8000/api/generate_reports

A fim de possibilitar geração dos arquivos posterior ao processamento dos dados, foi criado o endpoint: api/generate_reports, o mesmo é responsável por estar realizando a verificação da existência de dados no banco de dados e em caso de não existirem retornar uma mensagem solicitando que seja verificado se fora realizado o processamento pelo endpoint: api/process_resquests, também é responsável pela recuperação das informações dos relatórios realizando a criação de um arquivo csv, após a criação serão retornados os paths dos mesmos.

**Exemplo de Solicitação** 
> *Também é possível estar utilizando o Postman ou Insomnia*
```bash
# OBS: Executar fora do container

curl --location --request GET 'http://localhost:8000/api/generate_reports'
```

1. Com a requisições executadas será possível estar realizando a visualização dos relatórios atráves dos seguintes comandos:
```bash
# OBS: Executar no container

# Requisições por consumidor
    # Para visualizar todos os dados
      cat storage/reports/requests-by-consumer.csv
      
    # Para visualizar somente os 15 primeiros
      head --lines=10 storage/reports/requests-by-consumer.csv
      
    # Para visualizar somente os 15 últimos
      tail --lines=10 storage/reports/requests-by-consumer.csv
      
# Requisições por serviço
  cat storage/reports/requests-by-service.csv
  
# Tempo médio de request, proxy e gateway/kong por servico
  cat storage/reports/requests-with-average-latency-by-service.csv
```

## Considerações gerais

Entendo que o projeto em questão foi bastante importante para que eu possa estar colocando alguns conhecimentos em prática e também para estar considerando alguns pontos no momento de desenvolvimento - o que foi o caso de como realizar o processamento das informações. Por conta disso, agradeço a oportunidade de ter recebido tal desafio. Fico a disposição para possíveis dúvidas que surgirem.
