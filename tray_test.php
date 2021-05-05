<?php
//Verificar se os dados estão sendo enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (isset($_POST["id"]) && $_POST["id"] != null) ? $_POST["id"] : "";
    $nome = (isset($_POST["nome"]) && $_POST["nome"] != null) ? $_POST["nome"] : "";
    $email = (isset($_POST["email"]) && $_POST["email"] != null) ? $_POST["email"] : "";
    $valor = (isset($_POST["valor"]) && $_POST["valor"] != null) ? $_POST["valor"] : "";
    $comissao = (isset($_POST["comissao"]) && $_POST["comissao"] != null) ? $_POST["comissao"] : "";
    $dia = (isset($_POST["dia"]) && $_POST["dia"] != null) ? $_POST["dia"] : "";
    $id_vend = (isset($_POST["id_vend"]) && $_POST["id_vend"] != null) ? $_POST["id_vend"] : "";
} else if (!isset($id)) {
    //Se não se foi setado nenhum valor para a variável id
    $id = (isset($_GET["id"]) && $_GET["id"] != null) ? $_GET["id"] : "";
    $nome = NULL;
    $email = NULL;
    $valor = NULL;
    $porcentagem = 8.5;
    $comissao = $valor + ($valor * $porcentagem / 100);
    $dia = date("d/m/y");
}

//Cria a conexão com o banco de dados
try {
    $host = "127.0.0.1";
    $port = 3306;
    $socket = "";
    $user = "root";
    $password = "123456";
    $dbname = "tray_db";

    $con = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die('Could not connect to the database server' . mysqli_connect_error());
    $conexao = new PDO($con);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao->exec("set names utf8");
} catch (PDOException $erro) {
    echo "Erro na conexão: " . $erro->getMessage();
}
$query = "CREATE DATABASE IF NOT EXISTS tray_db DEFAULT CHARACTER SET utf8; 
          USE tray_db; CREATE TABLE IF NOT EXISTS Vendedores (id INT NOT NULL AUTO_INCREMENT, 
          nome VARCHAR(45) NOT NULL, email VARCHAR(45), PRIMARY KEY (id) ); 
          CREATE TABLE IF NOT EXISTS Vendas (id INT NOT NULL AUTO_INCREMENT, valor FLOAT NOT NULL, 
          comissao NOT NULL, dia DATE NOT NULL, id_vendedor INT NOT NULL, PRIMARY KEY (id))";


if ($stmt = $con->prepare($query)) {
    $stmt->execute();
    $stmt->bind_result($field1, $field2);
    while ($stmt->fetch()) {
        //printf("%s, %s\n", $field1, $field2);
    }
    $stmt->close();
}
if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "save" && $nome != "") {
    try {
        if ($id != "") {
            $stmt = $conexao->prepare("UPDATE Vendedores SET nome=?, email=? WHERE id=?");
            $stmt->bindParam(3, $id);
            $stmt = $conexao->prepare("UPDATE Vendas SET valor=?, comissao=?, dia=?, id_vend=? WHERE id=?");
            $stmt->bindParam(3, $id);
        } else {
            $stmt = $conexao->prepare("INSERT INTO Vendedores (nome, email) VALUES (?, ?)");
            $stmt = $conexao->prepare("INSERT INTO Vendas (valor, comissão, dia, id_vend) VALUES (?, ?, ?, ?)");
            $stmt->bindParam(1, $nome);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(4, $valor);
            $stmt->bindParam(5, $comissao);
            $stmt->bindParam(6, $dia);
            $stmt->bindParam(7, $id_vend);
        }
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo "Dados cadastrados com sucesso!";
                $id = null;
                $nome = null;
                $email = null;
                $valor = null;
                $comissao = null;
                $dia = null;
                $id_vend = null;
            } else {
                echo "Erro ao tentar efetivar cadastro";
            }
        } else {
            throw new PDOException("Erro: Não foi possível executar a declaração sql");
        }
    } catch (PDOException $erro) {
        echo "Erro : " . $erro->getMessage();
    }
}

//recupera as informações no formulário
if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "upd" && $id != "") {
    try {
        $stmt = $conexao->prepare("SELECT * FROM Vendedores WHERE id=?");
        $stmt = $conexao->prepare("SELECT * FROM Vendas WHERE id=?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $rs = $stmt->fetch(PDO::FETCH_OBJ);
            $id = $rs->id;
            $nome = $rs->nome;
            $email = $rs->email;
            $valor = $rs->$valor;
            $comissao = $rs->$comissao;
            $dia = $rs->$dia;
            $id_vend = $rs->$id_vend;
        } else {
            throw new PDOException("Erro: Não foi possível executar a declaração SQL");
        }
    } catch (PDOException $erro) {
        echo "Erro : " . $erro->getMessage();
    }
}
// Bloco if utilizado pela etapa Delete
if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "del" && $id != "") {
    try {
        $stmt = $conexao->prepare("DELETE FROM Vendedores WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "Registo foi excluído com êxito";
            $id = null;
        } else {
            throw new PDOException("Erro: Não foi possível executar a declaração sql");
        }
    } catch (PDOException $erro) {
        echo "Erro: " . $erro->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Vendedores</title>
</head>

<body>
    <form action="?act=save" method="POST" name="form1">
        <h1>Cadastro de Vendedores</h1>
        <hr>
        <input type="hidden" name="id" <?php
                                        //Preenche o id no campo "id"
                                        if (isset($id) && $id != null || $id != "") {
                                            echo "value=\"{$id}\"";
                                        }
                                        ?> />
        Nome:
        <input type="text" name="nome" <?php
                                        //Preenche o nome
                                        if (isset($nome) && $nome != null || $nome != "") {
                                            echo "value\"{$nome}\"";
                                        }
                                        ?> />
        E-mail:
        <input type="text" name="email" <?php
                                        //Preenche o email
                                        if (isset($email) && $email != null || $email != "") {
                                            echo "value=\"{$email}\"";
                                        }
                                        ?> />
        Valor:
        <input type="int" name="valor" <?php
                                        //Preenche as vendas
                                        if (isset($valor) && $valor != null || $valor != "") {
                                            echo "value=\"{$valor}\"";
                                        }
                                        ?> />
        Comissão:
        <input type="int" name="comissao" <?php
                                            //Preenche as comissões
                                            if (isset($comissao) && $comissao != null || $comissao != "") {
                                                echo "value=\"{$comissao}\"";
                                            }
                                            ?> />
        Dia:
        <input type="date" name="dia" <?php
                                        //Preenche o dia
                                        if (isset($dia) && $dia != null || $dia != "") {
                                            echo "value=\"{$dia}\"";
                                        }
                                        ?> />
        <input type="submit" value="Salvar" />
        <input type="reset" value="Novo" />
        <hr>
    </form>
    <table border="1" width="100%">
        <tr>
            <th id="nome">Nome</th>
            <th id="email">Email</th>
            <th id="valor">Valor</th>
            <th id="comissao">Comissão</th>
            <th id="dia">Dia</th>
            <th id="acoes">Ações</th>            
        </tr>
        <?php
        try {
            $stmt = $conexao->prepare("SELECT * FROM Vendedores");
            if ($stmt->execute()) {
                while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {
                    echo "<tr>";
                    echo "<td>" . $rs->nome . "</td><td>" . $rs->email . "</td><td>" . $rs->Valor . "</td><td>" . $rs->Comissão . "</td><td>" . $rs->Dia . "</td><td><center><a href=\"?act=upd&id=" . $rs->id . "\">[Alterar]</a>"
                        . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . "<a href=\"?act=del&id=" . $rs->id . "\">[Excluir]</a></center></td>";
                    echo "</tr>";
                }
            } else {
                echo "Erro: Não foi possível recuperar o banco de dados";
            }
        } catch (PDOException $erro) {
            echo "Erro: " . $erro->getMessage();
        }
        ?>
    </table>
</body>
</html>

