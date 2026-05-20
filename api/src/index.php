<?php
include_once 'config/database.php';

// Consulta para buscar os últimos registros de monitoramento, cruzando com hardware e cliente
$query = "
    SELECT 
        m.data_hora,
        m.latitude,
        m.longitude,
        m.image_url,
        m.status_cam,
        m.status_gps,
        h.mac,
        h.status AS hardware_status,
        c.nome AS cliente_nome,
        c.telefone AS cliente_telefone
    FROM monitoring m
    INNER JOIN hardware h ON m.id_hardware = h.id
    LEFT JOIN cliente_hardware ch ON h.id = ch.id_hardware AND ch.status = 'ativo'
    LEFT JOIN cliente c ON ch.id_cliente = c.id
    ORDER BY m.data_hora DESC
    LIMIT 100
";

$stmt = $conn->prepare($query);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Monitoramento - Wearable</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #e0e0e0; text-align: center; }
        th { background-color: #f8f9fa; font-weight: 600; }
        tr:nth-child(even) { background-color: #fcfcfc; }
        .img-preview { max-width: 80px; max-height: 80px; border-radius: 4px; object-fit: cover; cursor: pointer; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 0.75em; font-weight: bold; background: #28a745; color: white; }
        .badge.pendente { background: #ffc107; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Últimos Registros de Monitoramento</h1>
        
        <?php if (count($registros) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Cliente</th>
                        <th>MAC Hardware</th>
                        <th>Status Câm.</th>
                        <th>Status GPS</th>
                        <th>Localização (Lat / Lng)</th>
                        <th>Imagem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $row): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($row['data_hora'])); ?></td>
                            <td>
                                <?php if ($row['cliente_nome']): ?>
                                    <strong><?php echo htmlspecialchars($row['cliente_nome']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($row['cliente_telefone']); ?></small>
                                <?php else: ?>
                                    <span class="badge pendente">Sem Cliente Associado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['mac']); ?><br>
                                <span class="badge"><?php echo htmlspecialchars($row['hardware_status']); ?></span>
                            </td>
                            <td>
                                <span class="badge <?php echo $row['status_cam'] === 'on' ? '' : 'pendente'; ?>">
                                    <?php echo $row['status_cam'] === 'on' ? 'ON' : 'OFF'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $row['status_gps'] === 'on' ? '' : 'pendente'; ?>">
                                    <?php echo $row['status_gps'] === 'on' ? 'ON' : 'OFF'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['latitude'] && $row['longitude']): ?>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>" target="_blank">
                                        <?php echo $row['latitude']; ?><br><?php echo $row['longitude']; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="badge pendente">Sem Posição</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['image_url']): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($row['image_url']); ?>" target="_blank">
                                        <img src="uploads/<?php echo htmlspecialchars($row['image_url']); ?>" class="img-preview" alt="Captura">
                                    </a>
                                <?php else: ?>
                                    Sem imagem
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum dado de monitoramento recebido ainda.</p>
        <?php endif; ?>
    </div>
</body>
</html>