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
    console.log(user)
    socket.join(user.id)
    if (user.type == 'A') {
      socket.join('administrator')
    }
  })
  socket.on('loggedOut', function (user) {
    console.log(user)
    socket.leave(user.id)
    if (user.type == 'A') {
      socket.leave('administrator')
    }
  })

  socket.on('newTransaction', function (transaction) {
    console.log(transaction)
    if(transaction.payment_type == 'VCARD'){
      console.log('pair_vcard',transaction.pair_vcard)
      socket.in(parseInt(transaction.pair_vcard)).emit('newTransaction', transaction)
    }
    else{
      console.log('vcard',transaction.vcard)
      socket.in(transaction.vcard).emit('newTransaction', transaction)
    }
  })
  socket.on('updatedUser', function (user) {
    socket.in(user.id).emit('updatedUser', user)
  })
});
