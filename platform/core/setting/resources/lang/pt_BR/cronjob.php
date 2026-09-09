<?php

return [
    'name' => 'Cronjob',
    'description' => 'Configure tarefas automatizadas em segundo plano para manter seu site funcionando sem problemas.',
    'is_not_ready' => 'O cronjob ainda não está configurado',
    'is_not_ready_description' => 'Por favor, siga as instruções abaixo para configurar o cronjob. Isso é necessário para funcionalidades como lembretes de carrinho abandonado, agendamento de e-mails e outras tarefas automatizadas.',
    'is_working' => 'O cronjob está funcionando corretamente!',
    'is_not_working' => 'O cronjob parou de funcionar',
    'is_not_working_description' => 'O cronjob não foi executado nos últimos 10 minutos. Por favor, verifique a configuração do seu servidor ou entre em contato com seu provedor de hospedagem.',
    'last_checked' => 'Última atividade: :time',
    'copy_button' => 'Copiar comando',
    'what_is' => [
        'title' => 'O que é um Cronjob?',
        'description' => 'Um cronjob é uma tarefa automatizada que é executada em segundo plano no seu servidor. Ele permite que seu site execute tarefas importantes automaticamente sem que você precise fazer nada manualmente.',
        'examples' => 'Exemplos',
        'features' => 'Enviar lembretes de carrinho abandonado, processar e-mails agendados, limpar dados antigos, gerar relatórios e mais.',
    ],
    'command' => [
        'title' => 'Seu comando Cronjob',
        'description' => 'Copie este comando e adicione-o ao painel de controle da sua hospedagem. Este comando precisa ser executado a cada minuto para manter suas tarefas automatizadas funcionando.',
    ],
    'setup' => [
        'name' => 'Como configurar',
        'copied' => 'Copiado para a área de transferência!',
        'choose_hosting' => 'Selecione seu painel de controle de hospedagem abaixo e siga as instruções passo a passo:',
    ],
    'cpanel' => [
        'step1' => 'Faça login na sua conta <strong>cPanel</strong>',
        'step2' => 'Encontre e clique em <strong>"Cron Jobs"</strong> na seção "Advanced"',
        'step3' => 'Em "Add New Cron Job", selecione <strong>"Once Per Minute (* * * * *)"</strong> no menu suspenso',
        'step4' => '<strong>Cole o comando</strong> que você copiou acima no campo "Command"',
        'step5' => 'Clique em <strong>"Add New Cron Job"</strong> para salvar',
    ],
    'plesk' => [
        'step1' => 'Faça login no seu painel de controle <strong>Plesk</strong>',
        'step2' => 'Vá para <strong>"Scheduled Tasks"</strong> (ou "Cron Jobs")',
        'step3' => 'Clique em <strong>"Add Task"</strong> ou <strong>"Schedule a Task"</strong>',
        'step4' => 'Configure o agendamento para executar <strong>a cada minuto</strong> e cole o comando',
        'step5' => 'Clique em <strong>"OK"</strong> ou <strong>"Apply"</strong> para salvar',
    ],
    'directadmin' => [
        'step1' => 'Faça login no seu painel <strong>DirectAdmin</strong>',
        'step2' => 'Navegue para <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Clique em <strong>"Add Cron Job"</strong>',
        'step4' => 'Configure todos os campos de tempo para <strong>*</strong> (a cada minuto) e cole o comando',
        'step5' => 'Clique em <strong>"Add"</strong> para salvar o cronjob',
    ],
    'ssh' => [
        'step1' => 'Conecte-se ao seu servidor via <strong>SSH</strong> usando Terminal ou PuTTY',
        'step2' => 'Digite <code>crontab -e</code> e pressione Enter para editar o arquivo crontab',
        'step3' => 'Adicione uma nova linha no final e <strong>cole o comando</strong>',
        'step4' => 'Pressione <strong>Ctrl+X</strong>, depois <strong>Y</strong>, depois <strong>Enter</strong> para salvar (para o editor nano)',
        'step5' => 'O cronjob agora está ativo e será executado a cada minuto',
    ],
    'need_help' => 'Precisa de ajuda? Entre em contato com seu provedor de hospedagem e peça para configurar um cronjob que seja executado a cada minuto com o comando mostrado acima.',
];
