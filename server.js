const express = require('express');
const mysql= require('mysql2');
const bodyparser= require('body-parser');
const app = express();
app.use(bodyparser.json());
const nodemail = require("nodemailer");
const transporter = nodemail.createTransport({
host: "mail.drptdu.rw",
port:"456",
secure:"true",
auth:{
    user: "deploy@rptdu.rw",
    pass:"SOD2025"
}


});
transporter.verify((error,success)=>{
    if(error){
        console.error("SMTP SERVER IS READY TO TAKE MESSAGES");
    }
});
const db=mysql.createConnection({
    host:'localhost',
    user:'root',
    password:'',
    database:'l4sod',
});

db.connect((err)=>{
if(err){
console.error("DATABASE CONNECTION FAILED",err);
}
else{
    console.log("DATABASE CONNECTED");
    
}
});


app.get('/:name',(req,res)=>{
    const name=req.params.name;
    res.json({message:`WELCOME,${name}!`});
});

app.get('/students',(req,res)=>{
    db.query('SELECT * FROM students',(err,results)=>{
    if(err) return res.status(500).send(err);
    res.json(result);
});

app.post('/students',(req,res)=>{
const {name,email,age}= req.body;
db.query('INSERT INTO students (name,email,age) VALUES (?,?,?)',[name,email,age])
if(err) return res.status(500).send(err);
const insertedStudent={id: result.insertId,name,email,age};
const mailOptions={
    from: 'deploy@rptdu.rw',
    to: email,
    subject: "REGISTRATION ON KNAX 250 VIP SOFTWARE DEVELOPMENT",
    text: `Hello ${name}, \n\n  YOUR STUDENT ACCOUNT HAS BEEN CREATED SUCCESSFULLY. \n\n YOUR STUDENT ACCOUNT HAS BEEN CREATED SUCCESSFULLY`,
};
transporter.sendMail(mailOptions,(emailErr,info)=>{
    if(emailErr){
        console.error('ERROR SENDING EMAIL', emailErr);
        return req.status(500).json({
            error: 'error sending email but student added', insertedStudent
        });
    }
    console.log('EMAI SENT', info.response);
    res.status(201).json({
        message: 'student added and email sent',
        student: insertedStudent,
    });
});
});
});

app.put('/students/:id',(req,res)=>{
const{name,email,age}=req.body;
const{id}=req.params;
db.query('UPDATE students SET name= ?, email= ?, age= ?')
if(err) return res.status(500).send(err);
res.json({message:'student updated'});
});

app.delete('/students/:id',(req,res)=>{
const{id}=req.params;
});

const PORT=3000;
app.listen(PORT,()=>{
    console.log(`server is running on PORT ${PORT}`);
});

