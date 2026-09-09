<?php

return [
    'name' => 'Cronjob',
    'description' => 'Configure tarefas automatizadas em segundo plano para manter o seu site a funcionar sem problemas.',
    'is_not_ready' => 'O cronjob ainda não está configurado',
    'is_not_ready_description' => 'Por favor, siga as instruções abaixo para configurar o cronjob. Isto é necessário para funcionalidades como lembretes de carrinho abandonado, agendamento de emails e outras tarefas automatizadas.',
    'is_working' => 'O cronjob está a funcionar corretamente!',
    'is_not_working' => 'O cronjob parou de funcionar',
    'is_not_working_description' => 'O cronjob não foi executado nos últimos 10 minutos. Por favor, verifique a configuração do seu servidor ou contacte o seu fornecedor de alojamento.',
    'last_checked' => 'Última atividade: :time',
    'copy_button' => 'Copiar comando',
    'what_is' => [
        'title' => 'O que é um Cronjob?',
        'description' => 'Um cronjob é uma tarefa automatizada que é executada em segundo plano no seu servidor. Permite que o seu site execute tarefas importantes automaticamente sem que tenha de fazer nada manualmente.',
        'examples' => 'Exemplos',
        'features' => 'Enviar lembretes de carrinho abandonado, processar emails agendados, limpar dados antigos, gerar relatórios e mais.',
    ],
    'command' => [
        'title' => 'O seu comando Cronjob',
        'description' => 'Copie este comando e adicione-o ao painel de controlo do seu alojamento. Este comando precisa de ser executado a cada minuto para manter as suas tarefas automatizadas a funcionar.',
    ],
    'setup' => [
        'name' => 'Como configurar',
        'copied' => 'Copiado para a área de transferência!',
        'choose_hosting' => 'Selecione o seu painel de controlo de alojamento abaixo e siga as instruções passo a passo:',
    ],
    'cpanel' => [
        'step1' => 'Inicie sessão na sua conta <strong>cPanel</strong>',
        'step2' => 'Encontre e clique em <strong>"Cron Jobs"</strong> na secção "Advanced"',
        'step3' => 'Em "Add New Cron Job", selecione <strong>"Once Per Minute (* * * * *)"</strong> no menu suspenso',
        'step4' => '<strong>Cole o comando</strong> que copiou acima no campo "Command"',
        'step5' => 'Clique em <strong>"Add New Cron Job"</strong> para guardar',
    ],
    'plesk' => [
        'step1' => 'Inicie sessão no seu painel de controlo <strong>Plesk</strong>',
        'step2' => 'Vá para <strong>"Scheduled Tasks"</strong> (ou "Cron Jobs")',
        'step3' => 'Clique em <strong>"Add Task"</strong> ou <strong>"Schedule a Task"</strong>',
        'step4' => 'Configure o agendamento para executar <strong>a cada minuto</strong> e cole o comando',
        'step5' => 'Clique em <strong>"OK"</strong> ou <strong>"Apply"</strong> para guardar',
    ],
    'directadmin' => [
        'step1' => 'Inicie sessão no seu painel <strong>DirectAdmin</strong>',
        'step2' => 'Navegue para <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Clique em <strong>"Add Cron Job"</strong>',
        'step4' => 'Configure todos os campos de tempo para <strong>*</strong> (a cada minuto) e cole o comando',
        'step5' => 'Clique em <strong>"Add"</strong> para guardar o cronjob',
    ],
    'ssh' => [
        'step1' => 'Conecte-se ao seu servidor via <strong>SSH</strong> usando Terminal ou PuTTY',
        'step2' => 'Digite <code>crontab -e</code> e pressione Enter para editar o ficheiro crontab',
        'step3' => 'Adicione uma nova linha no final e <strong>cole o comando</strong>',
        'step4' => 'Pressione <strong>Ctrl+X</strong>, depois <strong>Y</strong>, depois <strong>Enter</strong> para guardar (para o editor nano)',
        'step5' => 'O cronjob está agora ativo e será executado a cada minuto',
    ],
    'need_help' => 'Precisa de ajuda? Contacte o seu fornecedor de alojamento e peça-lhes para configurar um cronjob que seja executado a cada minuto com o comando mostrado acima.',
];
