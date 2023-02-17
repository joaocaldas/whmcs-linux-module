var express = require('express');
var router = express.Router();
var shell = require('shelljs');

// não precisa ter nada na base, então retorna 404
router.get('/', function (req, res, next) {
    res.status(404).send('Not found');
});

router.post('/suspend', function (req, res) {
    if (!req.body.domain) {
        res.status(200).json({ status: "error", message: "Houve um erro ao suspender a conta: domínio nao informado", code: 0 });
    }

    var cmd = "sed -i 's,root /var/www/" + req.body.domain + "/htdocs,root /var/www/suspended,g' /etc/nginx/sites-available/" + req.body.domain + " && systemctl restart nginx";
    var exec = shell.exec(cmd);

    if (exec.code == 0) {
        res.status(200).json({ status: "success", message: "Conta suspensa", code: 200 });
    } else {
        res.status(200).json({ status: "error", message: "Houve um erro ao suspender a conta", code: exec.code });
    }
});

router.post('/unsuspend', function (req, res) {
    if (!req.body.domain) {
        res.status(200).json({ status: "error", message: "Houve um erro ao suspender a conta: domínio nao informado", code: 0 });
    }

    var cmd = "sed -i 's,root /var/www/suspended,root /var/www/" + req.body.domain + "/htdocs,g' /etc/nginx/sites-available/" + req.body.domain + " && systemctl restart nginx";
    var exec = shell.exec(cmd);

    if (exec.code == 0) {
        res.status(200).json({ status: "success", message: "Suspensão de conta removida", code: 200 });
    } else {
        res.status(200).json({ status: "error", message: "Houve um erro ao remover a suspensão da conta", code: exec.code });
    }
});

module.exports = router;
