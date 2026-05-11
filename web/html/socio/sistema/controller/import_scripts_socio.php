<script src="controller/script/socio.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script src="./controller/script/jquery.inputmask.js"></script>
<script type="text/javascript">
    var ctx1 = document.getElementById('grafico1');
    console.log("gráficos");
    if (ctx1 != null && ctx1 != "") {
        
        ctx1 = ctx1.getContext('2d');
        var grafico1 = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: ['Sócios Mensais', 'Sócios Casuais', 'Sócios sem informação de contrib.', 'Sócios bimestrais', 'Sócios trimestrais', 'Sócios semestrais'],
                datasets: [{
                    label: '# of Votes',
                    data: [<?= ($mensal); ?>, <?= ($casual); ?>, <?= ($si_contrib); ?>, <?= ($bimestral); ?>, <?= ($trimestral); ?>, <?= ($semestral); ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(245, 213, 51, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(245, 213, 51, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    }
</script>