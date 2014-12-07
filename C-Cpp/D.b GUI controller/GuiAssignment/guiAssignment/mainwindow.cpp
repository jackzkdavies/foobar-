#include "mainwindow.h"
#include "ui_mainwindow.h"
#include <sstream>
#include <string.h>

MainWindow::MainWindow(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::MainWindow)
{
    ui->setupUi(this);
    PortSettings settings = {BAUD9600, DATA_8, PAR_NONE, STOP_1, FLOW_OFF, 10};
    port = new QextSerialPort("COM3", settings, QextSerialPort::EventDriven);
    connect(port, SIGNAL(readyRead()), SLOT(serialDataReady()));
    port->open(QIODevice::ReadWrite);
}


MainWindow::~MainWindow()
{
    delete ui;
    delete port; //Add  to delete or disconnect open port
}

void MainWindow::pushButtonClicked()
{
    QByteArray qba = ('M' + ui->LCDlineEdit->text()+'\n').toLatin1();
    port->write(qba.data());
}
void MainWindow::greenLedToggled()
{
    QString Green;
    Green = 'G';
    QByteArray qba=(Green + '\n').toLatin1();
    port->write(qba.data());
}
void MainWindow::blueLedToggled()
{
    QString Blue;
    Blue = 'B';
    QByteArray qba=(Blue + '\n').toLatin1();
    port->write(qba.data());
}
void MainWindow::servoDegree(int)
{
    int degree = ui->ServerController->value();
    QString s = QString::number(degree);
    QString Servo;
    Servo = 'S';

    QByteArray qba=(Servo + s + '\n').toLatin1();
    port->write(qba.data());
}

void MainWindow::stepperOnOff()
{

    QString Stepper;
    Stepper = 'C';

    QByteArray qba=(Stepper + '\n').toLatin1();
    port->write(qba.data());
}

void MainWindow::stepperDir()
{

    QString Stepper;
    Stepper = 'D';

    QByteArray qba=(Stepper + '\n').toLatin1();
    port->write(qba.data());
}

void MainWindow::stepperSpeed(int)
{
    int speed = ui->StepperSpeedController->value();
    QString s = QString::number(speed);
    QString stepper;
    stepper = 'R';

    QByteArray qba=(stepper + s + '\n').toLatin1();
    port->write(qba.data());
}

void MainWindow::serialDataReady()
{
    if(port->canReadLine()){
        char s[80];
        s[strlen(s)-1]='\0';
        port->readLine(s,80);
//        ui->lineEdit->setText(s);
    }
}
