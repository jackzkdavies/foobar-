#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>

#include "qextserialport.h"

namespace Ui {
class MainWindow;
}

class MainWindow : public QMainWindow
{
    Q_OBJECT
    
public:
    explicit MainWindow(QWidget *parent = 0);
    ~MainWindow();

    QextSerialPort *port;

public slots:
    // "event" - sgnal device wants to write!
    void serialDataReady();
    void pushButtonClicked();
    void greenLedToggled();
    void blueLedToggled();
    void servoDegree(int);
    void stepperOnOff();
    void stepperDir();
    void stepperSpeed(int);
    
private:
    Ui::MainWindow *ui;
};

#endif // MAINWINDOW_H
