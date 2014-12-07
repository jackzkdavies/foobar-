#include "stm32f10x.h"
#include "stm32f10x_rcc.h"
#include "stm32f10x_gpio.h"



//PC0 - PC3 for the 4 data lines – create some nice defines
#define LCD_D4 GPIO_Pin_0
#define LCD_D5 GPIO_Pin_1
#define LCD_D6 GPIO_Pin_2
#define LCD_D7 GPIO_Pin_3
// PC4 for the Enable strobe, PC5 for RS (H for data, L for instruction)
#define LCD_EN GPIO_Pin_4
#define LCD_RS GPIO_Pin_5

#define LCD_DATA 1
#define LCD_INSTRUCTION 0

void lcd_command(unsigned char command);
void lcd_putchar(unsigned char c);
void delay_microsec(register int n);
void wait_for_user();
void delay_millisec(register unsigned short n);

void initTimerAndInput();
void initLCD();
void displayTime(int m, int s, int h);
int div(int i);
int mod(int i);

static void set_RS(int mode); //mode is either LCD_DATA or LCD_INSTRUCTION
static void pulse_EN(void);
static void lcd_init_8_bit(unsigned char command); //the first few commands
								//of initialisation, are still
								//in pseudo 8bit data mode
int main() {

	initLCD();
	initTimerAndInput();

	while (1) {

		int m = 0;
		int s = 0;
		int h = 0;

		int i;
		for (i=0; i<1; i++){
			displayTime(m, s, h);
			wait_for_user();
		}

		while (! GPIO_ReadInputDataBit(GPIOA, GPIO_Pin_0)){

			if (h == 100) {
				s++;
				h = 0;
				delay_millisec(40); // make up for processing delays
			}
			if (s == 60){
				m++;
				s=0;
			}

			h ++;

			displayTime(m, s, h);
			delay_millisec(7); //resetting display requires 2ms, 8ms delay results in stopwatch
							// running slow, 7ms with 40ms delay every second corrects this
		}

		wait_for_user(); //removes bounce effect

		for (i=0; i<1; i++){
			displayTime(m, s, h);
			wait_for_user();
		}

	}
}

void displayTime(int m, int s, int h){

	lcd_command(0x01); // clears display
	delay_millisec(2);

	lcd_putchar(div(m));
	lcd_putchar(mod(m));
	lcd_putchar(':');
	lcd_putchar(div(s));
	lcd_putchar(mod(s));
	lcd_putchar(':');
	lcd_putchar(div(h));
	lcd_putchar(mod(h));

}
//return tenthes as an ASCII
int div(int i){
	char c;
	c = (i/10)+ '0' ;
	return c;
}
//return ones as an ASCII
int mod(int i){
	char c;
	c = (i%10)+ '0' ;
	return c;
}
static void set_RS(int mode) {
	GPIO_WriteBit(GPIOC, LCD_RS, mode);
}

static void pulse_EN(void) {
	GPIO_WriteBit(GPIOC, LCD_EN, Bit_SET);
	delay_microsec(1);			// enable pulse must be >450ns
	GPIO_WriteBit(GPIOC, LCD_EN, Bit_RESET);
	delay_microsec(1);
}

static void lcd_init_8_bit(unsigned char command) {
	set_RS(LCD_INSTRUCTION);
	GPIO_WriteBit(GPIOC, LCD_D4, (command>>4) & 0x01);	//bottom 4 bits
	GPIO_WriteBit(GPIOC, LCD_D5, (command>>5) & 0x01);	//are ignored
	GPIO_WriteBit(GPIOC, LCD_D6, (command>>6) & 0x01);
	GPIO_WriteBit(GPIOC, LCD_D7, (command>>7) & 0x01);
	pulse_EN();
	delay_microsec(37); 			//let it work on the data
}

void lcd_command(unsigned char command) {
	set_RS(LCD_INSTRUCTION);
	GPIO_WriteBit(GPIOC, LCD_D4, (command>>4) & 0x01);	//first the top
	GPIO_WriteBit(GPIOC, LCD_D5, (command>>5) & 0x01);	//4 bits
	GPIO_WriteBit(GPIOC, LCD_D6, (command>>6) & 0x01);
	GPIO_WriteBit(GPIOC, LCD_D7, (command>>7) & 0x01);
	pulse_EN();
	GPIO_WriteBit(GPIOC, LCD_D4, command & 0x01);		//now the bottom
	GPIO_WriteBit(GPIOC, LCD_D5, (command>>1) & 0x01);	//4 bits
	GPIO_WriteBit(GPIOC, LCD_D6, (command>>2) & 0x01);
	GPIO_WriteBit(GPIOC, LCD_D7, (command>>3) & 0x01);
	pulse_EN();
	delay_microsec(37);				//let it work on the data
}

void lcd_putchar(unsigned char c) {
	set_RS(LCD_DATA);
	GPIO_WriteBit(GPIOC, LCD_D4, (c>>4) & 0x01);	//top 4 bits go first
	GPIO_WriteBit(GPIOC, LCD_D5, (c>>5) & 0x01);
	GPIO_WriteBit(GPIOC, LCD_D6, (c>>6) & 0x01);
	GPIO_WriteBit(GPIOC, LCD_D7, (c>>7) & 0x01);
	pulse_EN();										//this can't be too slow or it will time out
	GPIO_WriteBit(GPIOC, LCD_D4, c & 0x01);  //bottom 4 bits last
	GPIO_WriteBit(GPIOC, LCD_D5, (c>>1) & 0x01);
	GPIO_WriteBit(GPIOC, LCD_D6, (c>>2) & 0x01);
	GPIO_WriteBit(GPIOC, LCD_D7, (c>>3) & 0x01);
	pulse_EN();
	delay_microsec(37);				//let it work on the data
}

void delay_microsec(register int n) {
	register int i;
	for (i=0; i<n; i++) {
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
		asm("nop");
	}
}
void wait_for_user()
{
	while (! GPIO_ReadInputDataBit(GPIOA, GPIO_Pin_0));
	while (GPIO_ReadInputDataBit(GPIOA, GPIO_Pin_0))
	{
		delay_millisec(2);
	}
	return;
}
void delay_millisec(register unsigned short n)
{
	if (n > 1) n--;
	TIM6->PSC = 23999; // Set prescaler to 24,000 (PSC + 1)
	TIM6->ARR = n; // n = 1 gives 2msec delay rest are ok
	TIM6->CNT = 0;
	TIM6->EGR = TIM_EGR_UG;// copy values into actual registers!
	// Enable timer in one pulse mode
	TIM6->CR1 |= (TIM_CR1_OPM | TIM_CR1_CEN);
	while (TIM6->CR1 & TIM_CR1_CEN);// wait for it to switch off
}
void initTimerAndInput()
{
	RCC_APB2PeriphClockCmd(RCC_APB2Periph_GPIOA, ENABLE);

	GPIO_InitTypeDef GPIO_InitStructure;
	GPIO_StructInit(&GPIO_InitStructure);
	GPIO_InitStructure.GPIO_Speed = GPIO_Speed_2MHz;

	GPIO_InitStructure.GPIO_Pin = GPIO_Pin_0;
	GPIO_InitStructure.GPIO_Mode = GPIO_Mode_IN_FLOATING;
	GPIO_Init(GPIOA, &GPIO_InitStructure);
}
void initLCD()
{
	RCC_APB2PeriphClockCmd(RCC_APB2Periph_GPIOC, ENABLE);
	RCC_APB1PeriphClockCmd(RCC_APB1Periph_TIM6, ENABLE);

	GPIO_InitTypeDef GPIO_InitStructure;
	GPIO_InitStructure.GPIO_Speed = GPIO_Speed_2MHz;
	GPIO_InitStructure.GPIO_Pin = LCD_D7 | LCD_D6 | LCD_D5 | LCD_D4 |
							LCD_EN | LCD_RS;
	GPIO_InitStructure.GPIO_Mode = GPIO_Mode_Out_PP;
	GPIO_Init(GPIOC, &GPIO_InitStructure);
	GPIO_WriteBit(GPIOC, LCD_EN, Bit_RESET);
	delay_microsec(15000);	//delay for >15msec second after power on

	lcd_init_8_bit(0x30);		//we are in "8bit" mode
	delay_microsec(4100);			//4.1msec delay
	lcd_init_8_bit(0x30);		//- but the bottom 4 bits are ignored
	delay_microsec(100);			//100usec delay
	lcd_init_8_bit(0x30);
	lcd_init_8_bit(0x20);
	lcd_command(0x28);		//we are now in 4bit mode, dual line
	lcd_command(0x08);		//display off
	lcd_command(0x01);		//display clear
	delay_microsec(2000);			//needs a 2msec delay !!
	lcd_command(0x06);		//cursor increments
	lcd_command(0x0f);		//display on, cursor(blinks) on
}
