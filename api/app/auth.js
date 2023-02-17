module.exports = function(req, res, next) {
    var pass = "";
    if (req.header('X-Auth-Token') == pass) {
        res.setHeader('X-Auth-Token', pass)
        next();
    } else {
        res.status(401).json({
            message: "Not Authorized",
            code: 400
        })
    }
}