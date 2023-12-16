const httpServer = require("http").createServer();
const io = require("socket.io")(httpServer, {
  cors: {
    // The origin is the same as the Vue app domain. Change if necessary
    origin: "http://localhost:5173",
    methods: ["GET", "POST"],
    credentials: true,
  },
});
httpServer.listen(8080, () => {
  console.log("listening on *:8080");
});

io.on("connection", (socket) => {
  console.log(`client ${socket.id} has connected`);

  socket.on('loggedIn', function (user) {
    socket.join(user.id)
    if (user.type == 'A') {
      socket.join('administrator')
    }
  })
  socket.on('loggedOut', function (user) {
    socket.leave(user.id)
    if (user.type == 'A') {
      socket.leave('administrator')
    }
  })

  socket.on('newTransaction', function (transaction) {
    if(transaction.payment_type == 'VCARD'){
      socket.in(parseInt(transaction.pair_vcard)).emit('newTransaction', transaction)
    }
    else{
      socket.in(transaction.vcard).emit('newTransaction', transaction)
    }
  })
  socket.on('blockedUser', function (user) {
    socket.in(user.phone_number).emit('blockedUser', user)
  })
  socket.on('deletedUser', function (userId) {
    socket.in(userId).emit('deletedUser', userId)
    socket.leave(userId)
    if(userId < 900000001){
      socket.leave('administrator')
    }
  })
});
